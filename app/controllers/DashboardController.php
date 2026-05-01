<?php
require_once BASE_PATH . '/app/models/Transaccion.php';
require_once BASE_PATH . '/app/models/Cuenta.php';
require_once BASE_PATH . '/app/models/Negocio.php';
require_once BASE_PATH . '/app/models/Recordatorio.php';

class DashboardController extends BaseController {
    public function index(): void {
        $userId = $this->userId();

        $transModel  = new Transaccion();
        $cuentaModel = new Cuenta();
        $negocioModel= new Negocio();
        $recModel    = new Recordatorio();

        $totales      = $transModel->getTotalesByTipo($userId);
        $porNegocio   = $transModel->getTotalesByNegocio($userId);
        $ultimas      = $transModel->getUltimas($userId, 6);
        $cuentas      = $cuentaModel->getAll($userId);
        $negocios     = $negocioModel->getAll($userId);
        $proximos     = $recModel->getProximos($userId, 7);

        $saldoTotal  = array_sum(array_column($cuentas, 'saldo'));
        $balance     = $totales['ingreso'] - $totales['gasto'];

        $this->render('dashboard/index', compact(
            'totales','porNegocio','ultimas','cuentas','negocios',
            'proximos','saldoTotal','balance'
        ), 'Dashboard');
    }
}
