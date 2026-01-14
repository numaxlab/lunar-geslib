<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Http\Resources\Api\Geslib;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    #[\Override]
    public function toArray(Request $request): array
    {
        $shippingAddress = $this->resource->shippingAddress;
        $billingAddress = $this->resource->billingAddress;

        return [
            'codigo' => $this->resource->reference,
            'fecha' => $this->resource->placed_at->format('Y-m-d'),
            'hora' => $this->resource->placed_at->format('H:i:s'),
            'ip_origen' => '127.0.0.1',
            'cod_cliente' => $this->resource->customer?->meta?->offsetExists('geslib_id') ?
                $this->resource->customer->meta->offsetGet('geslib_id') : null,
            'nombre' => $shippingAddress?->first_name,
            'apellidos' => $shippingAddress?->last_name,
            'cif' => $shippingAddress?->tax_identifier,
            'direccion' => $shippingAddress ?
                $shippingAddress->line_one.' '.$shippingAddress->line_two.' '.$shippingAddress->line_three : null,
            'email' => $shippingAddress?->contact_email,
            'provincia' => $shippingAddress?->state,
            'localidad' => $shippingAddress?->city,
            'codigo_postal' => $shippingAddress?->postcode,
            'pais' => $shippingAddress?->country?->name,
            'telefono' => $shippingAddress?->contact_phone,
            'fax' => null,
            'nombre_fac' => $billingAddress?->full_name,
            'cif_fac' => $billingAddress?->tax_identifier,
            'direccion_fac' => $billingAddress ?
                $billingAddress->line_one.' '.$billingAddress->line_two.' '.$billingAddress->line_three : null,
            'email_fac' => $billingAddress?->contact_email,
            'provincia_fac' => $billingAddress?->state,
            'localidad_fac' => $billingAddress?->city,
            'codigo_postal_fac' => $billingAddress?->postcode,
            'pais_fac' => $billingAddress?->country?->name,
            'telefono_fac' => $billingAddress?->contact_phone,
            'fax_fac' => null,
            'envolver' => null,
            'enviar_factura' => (bool) $billingAddress,
            'cod_zona_envio' => null,
            'zona_envio' => null,
            'cod_forma_pago' => null,
            'forma_pago' => null,
            'cod_forma_envio' => null,
            'forma_envio' => null,
            'referencia_transferencia' => null,
            'numero_tarjeta' => null,
            'numero_trasero_tarjeta' => null,
            'titular' => null,
            'tipo_tarjeta' => null,
            'mes_tarjeta' => null,
            'anio_tarjeta' => null,
            'observaciones' => $this->resource->notes,
            'plazo_entrega' => null,
            'gastos_envio' => $this->resource->shipping_total,
            'observaciones_gastos_envio' => null,
            'estado' => 'C',
            'sincronizado' => 'N',
            'usuario_sincronizacion' => null,
            'fecha_sincronizacion' => null,
            'pedido_ebook' => 'N',
            'pedido_texto' => 'N',
            'codigo_iso_pais' => null,
            'idioma' => 'es',
            'cod_error_pasarela' => null,
            'descripcion_error_pasarela' => null,
            'token_pasarela' => null,
            'afiliado' => null,
            'cupon' => null,
            'descuento_cupon' => null,
            'importe_cupon' => null,
            'cupon_gastos' => null,
            'tarifa_plana_aplicada' => 'N',
            'puntos' => 0,
            'ebook_ref_pedido' => null,
            'mensaje_regalo' => null,
            'fecha_confirmacion' => $this->resource->placed_at->format('Y-m-d H:i:s'),
            'calle' => null,
            'num_exterior' => null,
            'num_interior' => null,
            'entre_calle1' => null,
            'entre_calle2' => null,
            'colonia' => null,
            'tlf_oficina' => null,
            'tlf_movil' => null,
            'horario_entrega' => null,
            'calle_fac' => null,
            'num_exterior_fac' => null,
            'num_interior_fac' => null,
            'entre_calle1_fac' => null,
            'entre_calle2_fac' => null,
            'colonia_fac' => null,
            'num_modificaciones' => 1,
            'paypal_transaction_id' => null,
            'cfdi' => null,
            'recargo_forma_pago' => null,
            'importe_recargo_forma_pago' => null,
            'cod_transportista' => null,
            'nombre_transportista' => null,
            'id_seguimiento' => null,
            'fecha_aviso_seguimiento' => null,
            'recoger_libreria' => 'N',
            'recibo' => null,
            'fac_papel' => null,
            'fac_electronica' => null,
            'movil' => null,
            'movil_fac' => null,
            'login' => null,
            'password' => null,
            'n_pedido' => $this->resource->reference,
            'cliente_web' => $this->resource->customer?->id,
            'cliente_geslib' => $this->resource->customer?->meta?->offsetExists('geslib_id') ?
                $this->resource->customer->meta->offsetGet('geslib_id') : null,
            'gastos_contrareembolso' => 0,
            'importe_total' => $this->resource->total,
        ];
    }
}
