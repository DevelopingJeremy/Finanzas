<?php
require_once BASE_PATH . '/app/models/Negocio.php';

class NegocioController extends BaseController {
    private Negocio $model;
    public function __construct() { $this->model = new Negocio(); }

    public function index(): void {
        $negocios = $this->model->getAll($this->userId());
        $this->render('negocios/index', ['negocios' => $negocios], 'Negocios');
    }

    public function show(): void {
        $id = (int)($_GET['id'] ?? 0);
        $negocio = $this->model->findById($id, $this->userId());
        if (!$negocio) {
            $this->flash('danger', 'Negocio no encontrado.');
            $this->redirect('/?c=negocios&a=index');
        }

        // Cuentas del negocio
        $cuentas = $this->model->getCuentas($id);

        // Cuenta seleccionada (puede venir por GET ?cuenta_id=X)
        $cuentaSelId = (int)($_GET['cuenta_id'] ?? ($cuentas[0]['id'] ?? 0));
        $cuentaSel   = null;
        $bolsillos   = [];
        $transacciones = [];
        $totalesCuenta = ['ingreso' => 0, 'gasto' => 0, 'transferencia' => 0];

        if ($cuentaSelId) {
            foreach ($cuentas as $c) {
                if ((int)$c['id'] === $cuentaSelId) { $cuentaSel = $c; break; }
            }
            if ($cuentaSel) {
                $bolsillos     = $this->model->getBolsillos($cuentaSelId);
                $transacciones = $this->model->getTransaccionesCuenta($cuentaSelId, $id);
                $totalesCuenta = $this->model->getTotalesCuenta($cuentaSelId, $id);
            }
        }

        // Recordatorios pendientes del negocio
        $recordatorios = $this->model->getRecordatoriosPendientes($id, $this->userId());

        // Resumen global del negocio
        $resumen = $this->model->getResumenNegocio($id, $this->userId());

        $this->render('negocios/show', compact(
            'negocio', 'cuentas', 'cuentaSel', 'cuentaSelId',
            'bolsillos', 'transacciones', 'totalesCuenta',
            'recordatorios', 'resumen'
        ), '🏢 ' . $negocio['nombre']);
    }

    public function create(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->required(['nombre'], $_POST);
            if (empty($errors)) {
                $ok = $this->model->create([
                    'usuario_id'  => $this->userId(),
                    'nombre'      => $this->clean($_POST['nombre']),
                    'tipo'        => $_POST['tipo'] ?? 'negocio',
                    'descripcion' => $this->clean($_POST['descripcion'] ?? ''),
                ]);
                $ok ? $this->flash('success', 'Negocio creado exitosamente.')
                    : $this->flash('danger', 'Error al crear el negocio.');
                $this->redirect('/?c=negocios&a=index');
            }
            $this->render('negocios/form', ['errors'=>$errors,'negocio'=>$_POST,'accion'=>'Crear'], 'Nuevo Negocio');
        } else {
            $this->render('negocios/form', ['negocio'=>[],'accion'=>'Crear','errors'=>[]], 'Nuevo Negocio');
        }
    }

    public function edit(): void {
        $id = (int)($_GET['id'] ?? 0);
        $negocio = $this->model->findById($id, $this->userId());
        if (!$negocio) { $this->flash('danger','Negocio no encontrado.'); $this->redirect('/?c=negocios&a=index'); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->required(['nombre'], $_POST);
            if (empty($errors)) {
                $this->model->update($id, $this->userId(), [
                    'nombre'      => $this->clean($_POST['nombre']),
                    'tipo'        => $_POST['tipo'] ?? 'negocio',
                    'descripcion' => $this->clean($_POST['descripcion'] ?? ''),
                    'activo'      => isset($_POST['activo']) ? 1 : 0,
                ]);
                $this->flash('success', 'Negocio actualizado.'); $this->redirect('/?c=negocios&a=index');
            }
            $this->render('negocios/form', ['errors'=>$errors,'negocio'=>$_POST,'accion'=>'Editar'], 'Editar Negocio');
        } else {
            $this->render('negocios/form', ['negocio'=>$negocio,'accion'=>'Editar','errors'=>[]], 'Editar Negocio');
        }
    }

    public function delete(): void {
        $id = (int)($_GET['id'] ?? 0);
        if ($this->model->findById($id, $this->userId())) {
            $this->model->delete($id, $this->userId());
            $this->flash('success', 'Negocio eliminado.');
        }
        $this->redirect('/?c=negocios&a=index');
    }
}
