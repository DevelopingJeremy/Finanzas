<?php
require_once BASE_PATH . '/app/services/FinanceAnalyticsService.php';

class ReporteController extends BaseController {
    public function index(): void {
        $userId = $this->userId();
        $mesActivo = $this->getMesActivo();
        $year  = $mesActivo['year'];
        $month = $mesActivo['month'];

        $analytics = new FinanceAnalyticsService();

        $totales = $analytics->getTotalesMes($userId, $year, $month);
        $comparativa = $analytics->getComparativaMesAnterior($userId, $year, $month);
        
        $gastosCategoria = $analytics->getGastosPorCategoria($userId, $year, $month);
        $ingresosCategoria = $analytics->getIngresosPorCategoria($userId, $year, $month);
        
        $gastosEmpresa = $analytics->getGastosPorEmpresa($userId, $year, $month);
        $ingresosEmpresa = $analytics->getIngresosPorEmpresa($userId, $year, $month);
        
        $gastosBolsillo = $analytics->getGastosPorBolsillo($userId, $year, $month);
        $ingresosBolsillo = $analytics->getIngresosPorBolsillo($userId, $year, $month);
        
        $distribucion = $analytics->getDistribucionDinero($userId);
        $totalesCuenta = $analytics->getTotalesPorCuenta($userId, $year, $month);

        $this->render('reportes/index', compact(
            'year', 'month', 'totales', 'comparativa',
            'gastosCategoria', 'ingresosCategoria',
            'gastosEmpresa', 'ingresosEmpresa',
            'gastosBolsillo', 'ingresosBolsillo',
            'distribucion', 'totalesCuenta'
        ), 'Reportes Mensuales');
    }
}
