<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Http\Resources\Api\Geslib;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderLineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'cod_linea' => $this->resource->id,
            'cod_pedido' => $this->resource->order->reference,
            'articulo' => $this->resource->purchasable->sku,
            'tipo_articulo' => null,
            'orden' => $this->additional['index'] ?? null,
            'cantidad' => $this->resource->quantity,
            'precio' => 38.00,
            'descripcion' => $this->resource->purchasable->getDescription(),
            'isbn' => $this->resource->purchasable->gtin,
            'ean' => $this->resource->purchasable->ean,
            'respetar_precio' => 'S',
            'descuento' => 0.00,
            'ebook_codigo' => null,
            'cod_distribuidora' => null,
            'ebook_formato' => null,
            'link_portada' => null,
            'cod_seguridad' => null,
            'estado_descarga' => null,
            'link_descarga' => null,
            'num_pedido_distribuidora' => null,
            'cancelado' => null,
            'num_ficheros' => 0,
            'precio_bruto_original' => null,
            'cupon' => null,
            'descuento_cupon' => 0.00,
            'disponibilidad' => null,
            'dispo_distri' => null,
            'ebook_drm' => null,
            'disponibilidad_web' => null,
            'precio_euros' => $this->resource->unit_price,
            'pvp_euros' => $this->resource->unit_price,
            'pvp_bruto' => $this->resource->sub_total->decimal(),
        ];
    }
}
