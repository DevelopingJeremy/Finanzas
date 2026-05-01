<?php
require_once BASE_PATH . '/app/models/Cliente.php';
require_once BASE_PATH . '/app/models/PagoCliente.php';
require_once BASE_PATH . '/app/models/Negocio.php';

class ClienteController extends BaseController {
    private Cliente $model;
    private PagoCliente $pagoModel;
    private Negocio $negocioModel;

    public function __construct() {
        $this->model        = new Cliente();
        $this->pagoModel    = new PagoCliente();
        $this->negocioModel = new Negocio();
    }

    public function index(): void {
        $clientes = $this->model->getAll($this->userId());
        $this->render('clientes/index', ['clientes' => $clientes], 'Clientes');
    }

    public function create(): void {
        $negocios = $this->negocioModel->getAll($this->userId());
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->required(['nombre'], $_POST);
            if (empty($errors)) {
                $this->model->create([
                    'usuario_id' => $this->userId(),
                    'negocio_id' => $_POST['negocio_id'] ?: null,
                    'nombre'     => $this->clean($_POST['nombre']),
                    'telefono'   => $this->clean($_POST['telefono'] ?? ''),
                    'email'      => $this->clean($_POST['email'] ?? ''),
                    'notas'      => $this->clean($_POST['notas'] ?? ''),
                ]);
                $this->flash('success','Cliente creado.'); $this->redirect('/?c=clientes&a=index');
            }
            $this->render('clientes/form', ['errors'=>$errors,'cliente'=>$_POST,'negocios'=>$negocios,'accion'=>'Crear'], 'Nuevo Cliente');
        } else {
            $this->render('clientes/form', ['cliente'=>[],'negocios'=>$negocios,'accion'=>'Crear','errors'=>[]], 'Nuevo Cliente');
        }
    }

    public function edit(): void {
        $id = (int)($_GET['id'] ?? 0);
        $cliente = $this->model->findById($id, $this->userId());
        if (!$cliente) { $this->flash('danger','Cliente no encontrado.'); $this->redirect('/?c=clientes&a=index'); }
        $negocios = $this->negocioModel->getAll($this->userId());

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->required(['nombre'], $_POST);
            if (empty($errors)) {
                $this->model->update($id, $this->userId(), [
                    'negocio_id' => $_POST['negocio_id'] ?: null,
                    'nombre'     => $this->clean($_POST['nombre']),
                    'telefono'   => $this->clean($_POST['telefono'] ?? ''),
                    'email'      => $this->clean($_POST['email'] ?? ''),
                    'notas'      => $this->clean($_POST['notas'] ?? ''),
                ]);
                $this->flash('success','Cliente actualizado.'); $this->redirect('/?c=clientes&a=index');
            }
            $this->render('clientes/form', ['errors'=>$errors,'cliente'=>$_POST,'negocios'=>$negocios,'accion'=>'Editar'], 'Editar Cliente');
        } else {
            $this->render('clientes/form', ['cliente'=>$cliente,'negocios'=>$negocios,'accion'=>'Editar','errors'=>[]], 'Editar Cliente');
        }
    }

    public function delete(): void {
        $id = (int)($_GET['id'] ?? 0);
        $this->model->delete($id, $this->userId());
        $this->flash('success','Cliente eliminado.'); $this->redirect('/?c=clientes&a=index');
    }

    /** Ver historial de pagos de un cliente */
    public function pagos(): void {
        $id      = (int)($_GET['id'] ?? 0);
        $cliente = $this->model->findById($id, $this->userId());
        if (!$cliente) { $this->flash('danger','Cliente no encontrado.'); $this->redirect('/?c=clientes&a=index'); }
        $pagos    = $this->pagoModel->getByCliente($id);
        $negocios = $this->negocioModel->getAll($this->userId());
        $this->render('clientes/pagos', compact('cliente','pagos','negocios'), 'Pagos de ' . $cliente['nombre']);
    }

    /** Crear pago para un cliente */
    public function crearPago(): void {
        $clienteId = (int)($_GET['cliente_id'] ?? 0);
        $cliente   = $this->model->findById($clienteId, $this->userId());
        if (!$cliente || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/?c=clientes&a=index');
        }
        $this->pagoModel->create([
            'cliente_id'  => $clienteId,
            'usuario_id'  => $this->userId(),
            'negocio_id'  => $_POST['negocio_id'] ?: null,
            'monto'       => (float)$_POST['monto'],
            'fecha'       => $_POST['fecha'],
            'estado'      => $_POST['estado'] ?? 'pendiente',
            'descripcion' => $this->clean($_POST['descripcion'] ?? ''),
        ]);
        $this->flash('success','Pago registrado.'); $this->redirect('/?c=clientes&a=pagos&id=' . $clienteId);
    }

    public function marcarPago(): void {
        $id        = (int)($_GET['id'] ?? 0);
        $clienteId = (int)($_GET['cliente_id'] ?? 0);
        $this->pagoModel->marcarPagado($id, $this->userId());
        $this->flash('success','Pago marcado como pagado.'); $this->redirect('/?c=clientes&a=pagos&id=' . $clienteId);
    }
}
