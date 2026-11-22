<?php

namespace App\Modules\Instituciones\Poblacion;

use RuntimeException;

/**
 * Rechazo detectado DENTRO de la operación protegida (tras bloquear filas):
 * aborta la transacción sin haber escrito nada. Si trae $lote, el plan ya
 * estaba aplicado (idempotencia); si no, trae los errores de validación.
 */
class RechazoPoblacion extends RuntimeException
{
    public function __construct(public readonly array $errores, public readonly ?string $lote = null)
    {
        parent::__construct($lote ? "Plan ya aplicado (lote {$lote})." : count($errores).' error(es) de validación.');
    }
}
