<?php
require_once BASE_PATH . '/app/models/Cuenta.php';
require_once BASE_PATH . '/app/models/Negocio.php';

class CuentaController extends BaseController {
    private Cuenta $model;
    private Negocio $negocioModel;

    public function __construct() {
        $this->model        = new Cuenta();
        $this->negocioModel = new Negocio();
    }

    public function index(): void {
        $cuentas = $this->model->getAll($this->userId());
        $this->render('cuentas/index', ['cuentas' => $cuentas], 'Cuentas');
    }

    public function create(): void {
        $negocios = $this->negocioModel->getAll($this->userId());
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->required(['nombre'], $_POST);
            if (empty($errors)) {
                $this->model->create([
                    'usuario_id'  => $this->userId(),
                    'negocio_id'  => $_POST['negocio_id'] ?: null,
                    'nombre'      => $this->clean($_POST['nombre']),
                    'tipo'        => $_POST['tipo'] ?? 'banco',
                    'saldo'       => (float)($_POST['saldo'] ?? 0),
                    'moneda'      => $this->clean($_POST['moneda'] ?? 'CRC'),
                ]);
                $this->flash('success', 'Cuenta creada.'); $this->redirect('/?c=cuentas&a=index');
            }
            $this->render('cuentas/form', ['errors'=>$errors,'cuenta'=>$_POST,'negocios'=>$negocios,'accion'=>'Crear'], 'Nueva Cuenta');
        } else {
            $this->render('cuentas/form', ['cuenta'=>[],'negocios'=>$negocios,'accion'=>'Crear','errors'=>[]], 'Nueva Cuenta');
        }
    }

    public function edit(): void {
        $id = (int)($_GET['id'] ?? 0);
        $cuenta = $this->model->findById($id, $this->userId());
        if (!$cuenta) { $this->flash('danger','Cuenta no encontrada.'); $this->redirect('/?c=cuentas&a=index'); }
        $negocios = $this->negocioModel->getAll($this->userId());

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->required(['nombre'], $_POST);
            if (empty($errors)) {
                $this->model->update($id, $this->userId(), [
                    'negocio_id' => $_POST['negocio_id'] ?: null,
                    'nombre'     => $this->clean($_POST['nombre']),
                    'tipo'       => $_POST['tipo'] ?? 'banco',
                    'moneda'     => $this->clean($_POST['moneda'] ?? 'CRC'),
                    'activo'     => isset($_POST['activo']) ? 1 : 0,
                ]);
                $this->flash('success','Cuenta actualizada.'); $this->redirect('/?c=cuentas&a=index');
            }
            $this->render('cuentas/form', ['errors'=>$errors,'cuenta'=>$_POST,'negocios'=>$negocios,'accion'=>'Editar'], 'Editar Cuenta');
        } else {
            $this->render('cuentas/form', ['cuenta'=>$cuenta,'negocios'=>$negocios,'accion'=>'Editar','errors'=>[]], 'Editar Cuenta');
        }
    }

    public function delete(): void {
        $id = (int)($_GET['id'] ?? 0);
        $this->model->delete($id, $this->userId());
        $this->flash('success','Cuenta desactivada.'); $this->redirect('/?c=cuentas&a=index');
    }
}
