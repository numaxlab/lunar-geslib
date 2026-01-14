<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Http\Resources\Api\Geslib;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderLineResource extends JsonResource
{
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'cod_linea' => $this->resource->id,
            'cod_pedido' => $this->resource->order->reference,
            'articulo' => $this->resource->purchasable->sku,
            'tipo_articulo' => $this->resource->purchasable->product->types->isNotEmpty() ?
                $this->resource->purchasable->product->types->first()->geslib_code : null,
            'orden' => $this->additional['index'] ?? null,
            'cantidad' => $this->resource->quantity,
            'precio' => $this->resource->unit_price->decimal(),
            'descripcion' => $this->resource->purchasable->getDescription(),
            'isbn' => $this->resource->purchasable->gtin,
            'ean' => $this->resource->purchasable->ean,
            'respetar_precio' => 'S',
            'descuento' => $this->resource->discount_total->decimal(),
            'ebook_codigo' => null,
            'cod_distribuidora' => 0,
            'ebook_formato' => null,
            'link_portada' => null,
            'cod_seguridad' => 0,
            'estado_descarga' => null,
            'link_descarga' => null,
            'num_pedido_distribuidora' => null,
            'cancelado' => 'N',
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
