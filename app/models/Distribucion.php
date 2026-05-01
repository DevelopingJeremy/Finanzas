<?php
/** Model: Distribucion (reparto de monto en subcuentas) */
class Distribucion {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }

    public function getByTransaccion(int $transaccionId): array {
        $stmt = $this->db->prepare(
            "SELECT d.*, s.nombre AS subcuenta_nombre
             FROM distribuciones d
             JOIN subcuentas s ON d.subcuenta_id = s.id
             WHERE d.transaccion_id = ?"
        );
        $stmt->execute([$transaccionId]);
        return $stmt->fetchAll();
    }

    public function create(int $transaccionId, int $subcuentaId, float $monto): bool {
        $stmt = $this->db->prepare("INSERT INTO distribuciones (transaccion_id, subcuenta_id, monto) VALUES (?,?,?)");
        return $stmt->execute([$transaccionId, $subcuentaId, $monto]);
    }

    public function deleteByTransaccion(int $transaccionId): bool {
        $stmt = $this->db->prepare("DELETE FROM distribuciones WHERE transaccion_id=?");
        return $stmt->execute([$transaccionId]);
    }
}
