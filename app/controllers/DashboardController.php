<?php
require_once BASE_PATH . '/app/services/FinanceAnalyticsService.php';
require_once BASE_PATH . '/app/models/Negocio.php';

class DashboardController extends BaseController {
    public function index(): void {
        $userId = $this->userId();
        $mesActivo = $this->getMesActivo();
        $year  = $mesActivo['year'];
        $month = $mesActivo['month'];

        $analytics = new FinanceAnalyticsService();

        // Saldo total real (NO depende del mes)
        $saldoTotal = $analytics->getSaldoTotal($userId);

        // Cuentas con bolsillos y disponible
        $cuentas = $analytics->getCuentasConBolsillos($userId);

        // Totales del mes activo
        $totales = $analytics->getTotalesMes($userId, $year, $month);
        $balance = $totales['balance'];

        // Últimas transacciones del mes activo
        $ultimas = $analytics->getUltimasTransacciones($userId, 6, $year, $month);

        // Resumen por negocio del mes activo
        $porNegocio = $analytics->getTotalesPorNegocioMes($userId, $year, $month);

        // Recordatorios próximos (siempre desde hoy, no depende del mes)
        $proximos = $analytics->getRecordatoriosProximos($userId, 7);

        // Comparación con mes anterior
        $comparativa = $analytics->getComparativaMesAnterior($userId, $year, $month);

        // Negocios para referencia
        $negocioModel = new Negocio();
        $negocios = $negocioModel->getAll($userId);

        $this->render('dashboard/index', compact(
            'totales','porNegocio','ultimas','cuentas','negocios',
            'proximos','saldoTotal','balance','comparativa',
            'year','month'
        ), 'Dashboard');
    }
}
