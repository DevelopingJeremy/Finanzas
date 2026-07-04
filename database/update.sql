-- SQL Update para Finanzas App - Reestructuración
-- Ejecutar en la base de datos `finanzas_app`

-- Agregar frecuencia 'anual' al enum de recordatorios
ALTER TABLE `recordatorios` MODIFY `frecuencia` ENUM('ninguna', 'diario', 'semanal', 'mensual', 'anual') DEFAULT 'ninguna';
