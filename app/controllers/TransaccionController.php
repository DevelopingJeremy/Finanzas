<?php
require_once BASE_PATH . '/app/models/Transaccion.php';
require_once BASE_PATH . '/app/models/Distribucion.php';
require_once BASE_PATH . '/app/models/Cuenta.php';
require_once BASE_PATH . '/app/models/Subcuenta.php';
require_once BASE_PATH . '/app/models/Negocio.php';
require_once BASE_PATH . '/app/models/Categoria.php';

class TransaccionController extends BaseController {
    private Transaccion $model;
    private Distribucion $distModel;
    private Cuenta $cuentaModel;
    private Subcuenta $subcuentaModel;
    private Negocio $negocioModel;
    private Categoria $categoriaModel;

    public function __construct() {
        $this->model          = new Transaccion();
        $this->distModel      = new Distribucion();
        $this->cuentaModel    = new Cuenta();
        $this->subcuentaModel = new Subcuenta();
        $this->negocioModel   = new Negocio();
        $this->categoriaModel = new Categoria();
    }

    public function index(): void {
        // Filtros
        $filters = [
            'negocio_id'  => $_GET['negocio_id']  ?? '',
            'cuenta_id'   => $_GET['cuenta_id']   ?? '',
            'tipo'        => $_GET['tipo']         ?? '',
            'fecha_desde' => $_GET['fecha_desde']  ?? '',
            'fecha_hasta' => $_GET['fecha_hasta']  ?? '',
        ];
        $transacciones = $this->model->getAll($this->userId(), $filters);
        $negocios      = $this->negocioModel->getAll($this->userId());
        $cuentas       = $this->cuentaModel->getAll($this->userId());
        $this->render('transacciones/index', compact('transacciones','negocios','cuentas','filters'), 'Transacciones');
    }

    public function create(): void {
        $negocios   = $this->negocioModel->getAll($this->userId());
        $cuentas    = $this->cuentaModel->getAll($this->userId());
        $subcuentas = $this->subcuentaModel->getAll($this->userId());
        $categorias = $this->categoriaModel->getAll($this->userId());

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->required(['cuenta_id','tipo','monto','fecha'], $_POST);
            if ((float)$_POST['monto'] <= 0) $errors[] = 'El monto debe ser mayor a 0.';

            $tipo               = $_POST['tipo'] ?? '';
            $cuentaId           = (int)$_POST['cuenta_id'];
            $subcuentaId        = !empty($_POST['subcuenta_id']) ? (int)$_POST['subcuenta_id'] : null;
            $cuentaDestinoId    = !empty($_POST['cuenta_destino_id']) ? (int)$_POST['cuenta_destino_id'] : null;
            $subcuentaDestinoId = !empty($_POST['subcuenta_destino_id']) ? (int)$_POST['subcuenta_destino_id'] : null;

            if ($tipo === 'transferencia') {
                if (empty($cuentaDestinoId)) {
                    $errors[] = 'Debe seleccionar una cuenta de destino.';
                } elseif ($cuentaId === $cuentaDestinoId && $subcuentaId === $subcuentaDestinoId) {
                    $errors[] = 'El origen y el destino de la transferencia no pueden ser iguales.';
                }
            }

            if (empty($errors)) {
                $monto = (float)$_POST['monto'];

                $id = $this->model->create([
                    'usuario_id'           => $this->userId(),
                    'negocio_id'           => $_POST['negocio_id'] ?: null,
                    'cuenta_id'            => $cuentaId,
                    'subcuenta_id'         => $subcuentaId,
                    'tipo'                 => $tipo,
                    'monto'                => $monto,
                    'fecha'                => $_POST['fecha'],
                    'descripcion'          => $this->clean($_POST['descripcion'] ?? ''),
                    'categoria_id'         => $_POST['categoria_id'] ?: null,
                    'cuenta_destino_id'    => $cuentaDestinoId,
                    'subcuenta_destino_id' => $subcuentaDestinoId,
                    'estado'               => 'completado',
                ]);

                if ($id) {
                    if ($tipo === 'ingreso') {
                        $this->cuentaModel->updateSaldo($cuentaId, $monto);
                        if ($subcuentaId) {
                            $this->subcuentaModel->updateSaldo($subcuentaId, $monto);
                            $this->distModel->create((int)$id, $subcuentaId, $monto);
                        }
                    } elseif ($tipo === 'gasto') {
                        $this->cuentaModel->updateSaldo($cuentaId, -$monto);
                        if ($subcuentaId) {
                            $this->subcuentaModel->updateSaldo($subcuentaId, -$monto);
                            $this->distModel->create((int)$id, $subcuentaId, $monto);
                        }
                    } elseif ($tipo === 'transferencia') {
                        if ($cuentaId === $cuentaDestinoId) {
                            // Transferencia dentro de la misma cuenta (entre bolsillos o bolsillo <-> cuenta normal)
                            // El saldo total de la cuenta principal permanece invariable.
                            if ($subcuentaId) {
                                $this->subcuentaModel->updateSaldo($subcuentaId, -$monto);
                            }
                            if ($subcuentaDestinoId) {
                                $this->subcuentaModel->updateSaldo($subcuentaDestinoId, $monto);
                            }
                        } else {
                            // Transferencia entre diferentes cuentas
                            $this->cuentaModel->updateSaldo($cuentaId, -$monto);
                            if ($subcuentaId) {
                                $this->subcuentaModel->updateSaldo($subcuentaId, -$monto);
                            }
                            $this->cuentaModel->updateSaldo($cuentaDestinoId, $monto);
                            if ($subcuentaDestinoId) {
                                $this->subcuentaModel->updateSaldo($subcuentaDestinoId, $monto);
                            }
                        }
                    }

                    // Procesar distribuciones múltiples en bolsillos (si se usó la sección dinámica)
                    if ($tipo !== 'transferencia' && empty($subcuentaId) && !empty($_POST['dist_subcuenta']) && is_array($_POST['dist_subcuenta'])) {
                        $delta = ($tipo === 'ingreso') ? $monto : -$monto;
                        foreach ($_POST['dist_subcuenta'] as $i => $scId) {
                            $dMonto = (float)($_POST['dist_monto'][$i] ?? 0);
                            if ($scId && $dMonto > 0) {
                                $this->distModel->create((int)$id, (int)$scId, $dMonto);
                                $this->subcuentaModel->updateSaldo((int)$scId, $delta > 0 ? $dMonto : -$dMonto);
                            }
                        }
                    }

                    $this->flash('success', 'Transacción registrada exitosamente.');
                    $this->redirect('/?c=transacciones&a=index');
                } else {
                    $errors[] = 'Error al guardar la transacción.';
                }
            }
            $this->render('transacciones/form', compact('errors','negocios','cuentas','subcuentas','categorias'), 'Nueva Transacción');
        } else {
            $this->render('transacciones/form', ['errors'=>[],'negocios'=>$negocios,'cuentas'=>$cuentas,'subcuentas'=>$subcuentas,'categorias'=>$categorias,'transaccion'=>[]], 'Nueva Transacción');
        }
    }

    public function delete(): void {
        $id = (int)($_GET['id'] ?? 0);
        $t  = $this->model->findById($id, $this->userId());
        if ($t) {
            $monto = (float)$t['monto'];
            $tipo  = $t['tipo'];

            if ($tipo === 'ingreso') {
                // Revertir saldo de cuenta principal
                $this->cuentaModel->updateSaldo((int)$t['cuenta_id'], -$monto);
                // Revertir distribuciones en bolsillos
                $distribuciones = $this->distModel->getByTransaccion($id);
                if (!empty($distribuciones)) {
                    foreach ($distribuciones as $dist) {
                        $this->subcuentaModel->updateSaldo((int)$dist['subcuenta_id'], -(float)$dist['monto']);
                    }
                } elseif (!empty($t['subcuenta_id'])) {
                    $this->subcuentaModel->updateSaldo((int)$t['subcuenta_id'], -$monto);
                }
            } elseif ($tipo === 'gasto') {
                // Revertir saldo de cuenta principal
                $this->cuentaModel->updateSaldo((int)$t['cuenta_id'], $monto);
                // Revertir distribuciones en bolsillos
                $distribuciones = $this->distModel->getByTransaccion($id);
                if (!empty($distribuciones)) {
                    foreach ($distribuciones as $dist) {
                        $this->subcuentaModel->updateSaldo((int)$dist['subcuenta_id'], (float)$dist['monto']);
                    }
                } elseif (!empty($t['subcuenta_id'])) {
                    $this->subcuentaModel->updateSaldo((int)$t['subcuenta_id'], $monto);
                }
            } elseif ($tipo === 'transferencia') {
                $cuentaOrigenId  = (int)$t['cuenta_id'];
                $cuentaDestinoId = !empty($t['cuenta_destino_id']) ? (int)$t['cuenta_destino_id'] : null;
                $scOrigenId      = !empty($t['subcuenta_id']) ? (int)$t['subcuenta_id'] : null;
                $scDestinoId     = !empty($t['subcuenta_destino_id']) ? (int)$t['subcuenta_destino_id'] : null;

                if ($cuentaDestinoId && $cuentaOrigenId === $cuentaDestinoId) {
                    // Transferencia interna en la misma cuenta: NO tocar la cuenta principal, solo revertir los bolsillos
                    if ($scOrigenId) {
                        $this->subcuentaModel->updateSaldo($scOrigenId, $monto);
                    }
                    if ($scDestinoId) {
                        $this->subcuentaModel->updateSaldo($scDestinoId, -$monto);
                    }
                } else {
                    // Transferencia entre distintas cuentas
                    $this->cuentaModel->updateSaldo($cuentaOrigenId, $monto);
                    if ($scOrigenId) {
                        $this->subcuentaModel->updateSaldo($scOrigenId, $monto);
                    }
                    if ($cuentaDestinoId) {
                        $this->cuentaModel->updateSaldo($cuentaDestinoId, -$monto);
                        if ($scDestinoId) {
                            $this->subcuentaModel->updateSaldo($scDestinoId, -$monto);
                        }
                    }
                }
            }

            // Eliminar el registro (las distribuciones se eliminan en cascada)
            $this->model->delete($id, $this->userId());
            $this->flash('success','Transacción eliminada y saldos revertidos.');
        }
        $this->redirect('/?c=transacciones&a=index');
    }
}
