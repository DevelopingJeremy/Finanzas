<?php
require_once BASE_PATH . '/app/models/Subcuenta.php';
require_once BASE_PATH . '/app/models/Cuenta.php';

class SubcuentaController extends BaseController {
    private Subcuenta $model;
    private Cuenta $cuentaModel;

    public function __construct() {
        $this->model       = new Subcuenta();
        $this->cuentaModel = new Cuenta();
    }

    public function index(): void {
        $subcuentas = $this->model->getAll($this->userId());
        $this->render('subcuentas/index', ['subcuentas' => $subcuentas], 'Bolsillos');
    }

    public function create(): void {
        $cuentas = $this->cuentaModel->getAll($this->userId());
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->required(['cuenta_id','nombre'], $_POST);
            if (empty($errors)) {
                $this->model->create([
                    'cuenta_id'   => (int)$_POST['cuenta_id'],
                    'nombre'      => $this->clean($_POST['nombre']),
                    'descripcion' => $this->clean($_POST['descripcion'] ?? ''),
                    'saldo'       => (float)($_POST['saldo'] ?? 0),
                ]);
                $this->flash('success','Bolsillo creado.'); $this->redirect('/?c=subcuentas&a=index');
            }
            $this->render('subcuentas/form', ['errors'=>$errors,'subcuenta'=>$_POST,'cuentas'=>$cuentas,'accion'=>'Crear'], 'Nuevo Bolsillo');
        } else {
            $this->render('subcuentas/form', ['subcuenta'=>[],'cuentas'=>$cuentas,'accion'=>'Crear','errors'=>[]], 'Nuevo Bolsillo');
        }
    }

    public function edit(): void {
        $id = (int)($_GET['id'] ?? 0);
        $subcuenta = $this->model->findById($id);
        if (!$subcuenta) { $this->flash('danger','Bolsillo no encontrado.'); $this->redirect('/?c=subcuentas&a=index'); }
        $cuentas = $this->cuentaModel->getAll($this->userId());

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->required(['cuenta_id','nombre'], $_POST);
            if (empty($errors)) {
                $this->model->update($id, [
                    'cuenta_id'   => (int)$_POST['cuenta_id'],
                    'nombre'      => $this->clean($_POST['nombre']),
                    'descripcion' => $this->clean($_POST['descripcion'] ?? ''),
                ]);
                $this->flash('success','Bolsillo actualizado.'); $this->redirect('/?c=subcuentas&a=index');
            }
            $this->render('subcuentas/form', ['errors'=>$errors,'subcuenta'=>$_POST,'cuentas'=>$cuentas,'accion'=>'Editar'], 'Editar Bolsillo');
        } else {
            $this->render('subcuentas/form', ['subcuenta'=>$subcuenta,'cuentas'=>$cuentas,'accion'=>'Editar','errors'=>[]], 'Editar Bolsillo');
        }
    }

    public function delete(): void {
        $id = (int)($_GET['id'] ?? 0);
        $this->model->delete($id);
        $this->flash('success','Bolsillo eliminado.'); $this->redirect('/?c=subcuentas&a=index');
    }

    /** AJAX: Retorna subcuentas de una cuenta en JSON */
    public function getByAccount(): void {
        $cuentaId = (int)($_GET['cuenta_id'] ?? 0);
        $data = $this->model->getByCuenta($cuentaId);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
