<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Http\Controllers\Api;

use Lunar\Models\Order;

class OrderController
{
    public function indexPending()
    {
        $orders = Order::where('is_geslib', true)
            ->whereIn('status', ['payment-received', 'dispatched'])
            ->whereNotNull('placed_at')
            ->orderBy('placed_at', 'desc')
            ->with(['customer'])
            ->get();

        if ($orders->isEmpty()) {
            return response()->xml([], 200, [], 'pedidosPendientes');
        }

        $pendingOrders = [
            'glmcpedcli' => [],
        ];

        foreach ($orders as $order) {
            $pendingOrders['glmcpedcli'][] = [
                'n_pedido' => $order->reference,
                'cliente' => $order->customer?->meta->offsetExists('geslib_id') ?
                    $order->customer->meta->offsetGet('geslib_id') : null,
            ];
        }

        return response()->xml($pendingOrders, 200, [], 'pedidosPendientes');
    }

    public function show($reference)
    {
        $order = Order::where('reference', $reference)
            ->where('is_geslib', true)
            ->whereNotNull('placed_at')
            ->with([
                'customer',
                'shippingAddress',
                'shippingAddress.country',
                'billingAddress',
                'billingAddress.country',
                'productLines',
                'productLines.purchasable',
            ])
            ->first();

        if (! $order) {
            return response()->xml([], 200, [], 'getPedido');
        }

        $geslibOrder = [
            'codigo' => $order->reference,
            'fecha' => $order->placed_at->format('Y-m-d'),
            'hora' => $order->placed_at->format('H:i:s'),
            'ip_origen' => '127.0.0.1',
            'cod_cliente' => $order->customer?->meta?->offsetExists('geslib_id') ?
                $order->customer->meta->offsetGet('geslib_id') : null,
            'nombre' => null,
            'apellidos' => null,
            'cif' => null,
            'direccion' => null,
            'email' => null,
            'provincia' => null,
            'localidad' => null,
            'codigo_postal' => null,
            'pais' => null,
            'telefono' => null,
            'fax' => null,
            'nombre_fac' => null,
            'cif_fac' => null,
            'direccion_fac' => null,
            'email_fac' => null,
            'provincia_fac' => null,
            'localidad_fac' => null,
            'codigo_postal_fac' => null,
            'pais_fac' => null,
            'telefono_fac' => null,
            'fax_fac' => null,
            'envolver' => null,
            'enviar_factura' => false,
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
            'observaciones' => $order->notes,
            'plazo_entrega' => null,
            'gastos_envio' => $order->shipping_total,
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
            'fecha_confirmacion' => $order->placed_at->format('Y-m-d H:i:s'),
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
            'n_pedido' => $order->reference,
            'cliente_web' => $order->customer?->id,
            'cliente_geslib' => $order->customer?->meta?->offsetExists('geslib_id') ?
                $order->customer->meta->offsetGet('geslib_id') : null,
            'gastos_contrareembolso' => 0,
            'importe_total' => $order->total,
        ];

        if ($order->shippingAddress) {
            $address = $order->shippingAddress;

            $geslibOrder = array_merge($geslibOrder, [
                'nombre' => $address->first_name,
                'apellidos' => $address->last_name,
                'cif' => $address->tax_identifier,
                'direccion' => $address->line_one.' '.$address->line_two.' '.$address->line_three,
                'email' => $address->contact_email,
                'provincia' => $address->state,
                'localidad' => $address->city,
                'codigo_postal' => $address->postcode,
                'pais' => $address->country->name,
                'telefono' => $address->contact_phone,
                'fax' => null,
                'calle' => null,
                'num_exterior' => null,
                'num_interior' => null,
                'entre_calle1' => null,
                'entre_calle2' => null,
                'colonia' => null,
                'tlf_oficina' => null,
                'tlf_movil' => null,
                'movil' => null,
            ]);
        }

        if ($order->billingAddress) {
            $address = $order->billingAddress;

            $geslibOrder = array_merge($geslibOrder, [
                'enviar_factura' => true,
                'nombre_fac' => $address->full_name,
                'cif_fac' => $address->tax_identifier,
                'direccion_fac' => $address->line_one.' '.$address->line_two.' '.$address->line_three,
                'email_fac' => $address->contact_email,
                'provincia_fac' => $address->state,
                'localidad_fac' => $address->city,
                'codigo_postal_fac' => $address->postcode,
                'pais_fac' => $address->country->name,
                'telefono_fac' => $address->contact_phone,
                'fax_fac' => null,
                'calle_fac' => null,
                'num_exterior_fac' => null,
                'num_interior_fac' => null,
                'entre_calle1_fac' => null,
                'entre_calle2_fac' => null,
                'colonia_fac' => null,
                'movil_fac' => null,
            ]);
        }

        $response = [
            'glmcpedcli' => $geslibOrder,
            'glmlpedcli' => [],
        ];

        foreach ($order->productLines as $index => $line) {
            $response['glmlpedcli'][] = [
                'cod_linea' => $line->id,
                'cod_pedido' => $order->reference,
                'articulo' => $line->purchasable->sku,
                'tipo_articulo' => null,
                'orden' => $index + 1,
                'cantidad' => $line->quantity,
                'precio' => 38.00,
                'descripcion' => $line->purchasable->getDescription(),
                'isbn' => $line->purchasable->gtin,
                'ean' => $line->purchasable->ean,
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
                'precio_euros' => $line->unit_price,
                'pvp_euros' => $line->unit_price,
                'pvp_bruto' => $line->sub_total->decimal(),
            ];
        }

        return response()->xml($response, 200, [], 'getPedido');
    }

    public function sync($reference)
    {
        $order = [];

        return response()->xml($order, 200, [], 'getPedido');
    }
}
