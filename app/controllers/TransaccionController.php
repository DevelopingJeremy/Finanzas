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
        $categorias = $this->categoriaModel->getAll($this->userId());

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->required(['cuenta_id','tipo','monto','fecha'], $_POST);
            if ((float)$_POST['monto'] <= 0) $errors[] = 'El monto debe ser mayor a 0.';

            if (empty($errors)) {
                $monto = (float)$_POST['monto'];
                $tipo  = $_POST['tipo'];

                $id = $this->model->create([
                    'usuario_id'       => $this->userId(),
                    'negocio_id'       => $_POST['negocio_id'] ?: null,
                    'cuenta_id'        => (int)$_POST['cuenta_id'],
                    'tipo'             => $tipo,
                    'monto'            => $monto,
                    'fecha'            => $_POST['fecha'],
                    'descripcion'      => $this->clean($_POST['descripcion'] ?? ''),
                    'categoria_id'     => $_POST['categoria_id'] ?: null,
                    'cuenta_destino_id'=> $_POST['cuenta_destino_id'] ?: null,
                    'estado'           => 'completado',
                ]);

                if ($id) {
                    // Actualizar saldo de cuenta origen
                    $delta = ($tipo === 'ingreso') ? $monto : -$monto;
                    if ($tipo !== 'transferencia') {
                        $this->cuentaModel->updateSaldo((int)$_POST['cuenta_id'], $delta);
                    } else {
                        // Transferencia: restar origen, sumar destino
                        $this->cuentaModel->updateSaldo((int)$_POST['cuenta_id'], -$monto);
                        if (!empty($_POST['cuenta_destino_id'])) {
                            $this->cuentaModel->updateSaldo((int)$_POST['cuenta_destino_id'], $monto);
                        }
                    }

                    // Procesar distribuciones en subcuentas
                    if (!empty($_POST['dist_subcuenta']) && is_array($_POST['dist_subcuenta'])) {
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
            $this->render('transacciones/form', compact('errors','negocios','cuentas','categorias'), 'Nueva Transacción');
        } else {
            $this->render('transacciones/form', ['errors'=>[],'negocios'=>$negocios,'cuentas'=>$cuentas,'categorias'=>$categorias,'transaccion'=>[]], 'Nueva Transacción');
        }
    }

    public function delete(): void {
        $id = (int)($_GET['id'] ?? 0);
        $t  = $this->model->delete($id, $this->userId());
        if ($t) {
            // Revertir saldo
            $monto = (float)$t['monto'];
            $tipo  = $t['tipo'];
            if ($tipo === 'ingreso')       $this->cuentaModel->updateSaldo($t['cuenta_id'], -$monto);
            if ($tipo === 'gasto')         $this->cuentaModel->updateSaldo($t['cuenta_id'],  $monto);
            if ($tipo === 'transferencia') {
                $this->cuentaModel->updateSaldo($t['cuenta_id'], $monto);
                if ($t['cuenta_destino_id']) $this->cuentaModel->updateSaldo($t['cuenta_destino_id'], -$monto);
            }
            $this->flash('success','Transacción eliminada y saldo revertido.');
        }
        $this->redirect('/?c=transacciones&a=index');
    }
}
