<?php
require_once BASE_PATH . '/app/models/Recordatorio.php';
require_once BASE_PATH . '/app/models/Negocio.php';
require_once BASE_PATH . '/app/models/Categoria.php';
require_once BASE_PATH . '/app/models/Cuenta.php';

class RecordatorioController extends BaseController {
    private Recordatorio $model;
    private Negocio $negocioModel;
    private Categoria $categoriaModel;
    private Cuenta $cuentaModel;

    public function __construct() {
        $this->model          = new Recordatorio();
        $this->negocioModel   = new Negocio();
        $this->categoriaModel = new Categoria();
        $this->cuentaModel    = new Cuenta();
    }

    public function index(): void {
        $recordatorios = $this->model->getAll($this->userId());
        $this->render('recordatorios/index', ['recordatorios' => $recordatorios], 'Recordatorios');
    }

    public function create(): void {
        $negocios   = $this->negocioModel->getAll($this->userId());
        $categorias = $this->categoriaModel->getAll($this->userId());
        $cuentas    = $this->cuentaModel->getAll($this->userId());
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->required(['nombre','tipo','monto','fecha_vencimiento'], $_POST);
            if (empty($errors)) {
                $this->model->create([
                    'usuario_id'       => $this->userId(),
                    'negocio_id'       => $_POST['negocio_id'] ?: null,
                    'tipo'             => $_POST['tipo'],
                    'nombre'           => $this->clean($_POST['nombre']),
                    'monto'            => (float)$_POST['monto'],
                    'fecha_vencimiento'=> $_POST['fecha_vencimiento'],
                    'frecuencia'       => $_POST['frecuencia'] ?? 'ninguna',
                    'categoria_id'     => $_POST['categoria_id'] ?: null,
                    'cuenta_id'        => $_POST['cuenta_id'] ?: null,
                ]);
                $this->flash('success','Recordatorio creado.'); $this->redirect('/?c=recordatorios&a=index');
            }
            $this->render('recordatorios/form', compact('errors','negocios','categorias','cuentas') + ['recordatorio'=>$_POST,'accion'=>'Crear'], 'Nuevo Recordatorio');
        } else {
            $this->render('recordatorios/form', ['recordatorio'=>[],'negocios'=>$negocios,'categorias'=>$categorias,'cuentas'=>$cuentas,'accion'=>'Crear','errors'=>[]], 'Nuevo Recordatorio');
        }
    }

    public function edit(): void {
        $id = (int)($_GET['id'] ?? 0);
        $rec = $this->model->findById($id, $this->userId());
        if (!$rec) { $this->flash('danger','No encontrado.'); $this->redirect('/?c=recordatorios&a=index'); }
        $negocios   = $this->negocioModel->getAll($this->userId());
        $categorias = $this->categoriaModel->getAll($this->userId());
        $cuentas    = $this->cuentaModel->getAll($this->userId());

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->required(['nombre','tipo','monto','fecha_vencimiento'], $_POST);
            if (empty($errors)) {
                $this->model->update($id, $this->userId(), [
                    'negocio_id'       => $_POST['negocio_id'] ?: null,
                    'tipo'             => $_POST['tipo'],
                    'nombre'           => $this->clean($_POST['nombre']),
                    'monto'            => (float)$_POST['monto'],
                    'fecha_vencimiento'=> $_POST['fecha_vencimiento'],
                    'frecuencia'       => $_POST['frecuencia'] ?? 'ninguna',
                    'categoria_id'     => $_POST['categoria_id'] ?: null,
                    'cuenta_id'        => $_POST['cuenta_id'] ?: null,
                ]);
                $this->flash('success','Recordatorio actualizado.'); $this->redirect('/?c=recordatorios&a=index');
            }
            $this->render('recordatorios/form', compact('errors','negocios','categorias','cuentas') + ['recordatorio'=>$_POST,'accion'=>'Editar'], 'Editar Recordatorio');
        } else {
            $this->render('recordatorios/form', ['recordatorio'=>$rec,'negocios'=>$negocios,'categorias'=>$categorias,'cuentas'=>$cuentas,'accion'=>'Editar','errors'=>[]], 'Editar Recordatorio');
        }
    }

    public function marcarPagado(): void {
        $id = (int)($_GET['id'] ?? 0);
        $this->model->marcarPagado($id, $this->userId());
        $this->flash('success','Recordatorio marcado como pagado.'); $this->redirect('/?c=recordatorios&a=index');
    }

    public function delete(): void {
        $id = (int)($_GET['id'] ?? 0);
        $this->model->delete($id, $this->userId());
        $this->flash('success','Recordatorio eliminado.'); $this->redirect('/?c=recordatorios&a=index');
    }
}
