<?php
require_once BASE_PATH . '/app/models/Categoria.php';

class CategoriaController extends BaseController {
    private Categoria $model;
    public function __construct() { $this->model = new Categoria(); }

    public function index(): void {
        $categorias = $this->model->getAll($this->userId());
        $this->render('categorias/index', ['categorias' => $categorias], 'Categorías');
    }

    public function create(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->required(['nombre','tipo'], $_POST);
            if (empty($errors)) {
                $this->model->create([
                    'usuario_id' => $this->userId(),
                    'nombre'     => $this->clean($_POST['nombre']),
                    'tipo'       => $_POST['tipo'],
                    'color'      => $this->clean($_POST['color'] ?? '#10b981'),
                    'icono'      => $this->clean($_POST['icono'] ?? '💰'),
                ]);
                $this->flash('success','Categoría creada.'); $this->redirect('/?c=categorias&a=index');
            }
            $this->render('categorias/form', ['errors'=>$errors,'categoria'=>$_POST,'accion'=>'Crear'], 'Nueva Categoría');
        } else {
            $this->render('categorias/form', ['categoria'=>[],'accion'=>'Crear','errors'=>[]], 'Nueva Categoría');
        }
    }

    public function edit(): void {
        $id = (int)($_GET['id'] ?? 0);
        $categoria = $this->model->findById($id);
        if (!$categoria) { $this->flash('danger','Categoría no encontrada.'); $this->redirect('/?c=categorias&a=index'); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->required(['nombre','tipo'], $_POST);
            if (empty($errors)) {
                $this->model->update($id, [
                    'nombre' => $this->clean($_POST['nombre']),
                    'tipo'   => $_POST['tipo'],
                    'color'  => $this->clean($_POST['color'] ?? '#10b981'),
                    'icono'  => $this->clean($_POST['icono'] ?? '💰'),
                ]);
                $this->flash('success','Categoría actualizada.'); $this->redirect('/?c=categorias&a=index');
            }
            $this->render('categorias/form', ['errors'=>$errors,'categoria'=>$_POST,'accion'=>'Editar'], 'Editar Categoría');
        } else {
            $this->render('categorias/form', ['categoria'=>$categoria,'accion'=>'Editar','errors'=>[]], 'Editar Categoría');
        }
    }

    public function delete(): void {
        $id = (int)($_GET['id'] ?? 0);
        $ok = $this->model->delete($id, $this->userId());
        $ok ? $this->flash('success','Categoría eliminada.') : $this->flash('warning','No se puede eliminar una categoría por defecto.');
        $this->redirect('/?c=categorias&a=index');
    }
}
