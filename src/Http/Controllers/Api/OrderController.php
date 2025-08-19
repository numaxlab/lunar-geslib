<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Http\Controllers\Api;

use Lunar\Models\Order;

class OrderController
{
    public function indexPending()
    {
        $orders = Order::whereIn('status', ['payment-received', 'dispatched'])
            ->whereNotNull('placed_at')
            ->orderBy('placed_at', 'desc')
            ->with(['customer'])
            ->get();

        $pendingOrders = [
            'glmcpedcli' => [],
        ];

        foreach ($orders as $order) {
            $pendingOrders['glmcpedcli'][] = [
                'n_pedido' => $order->reference,
                'cliente' => $order->customer->meta->offsetExists('geslib_id') ?
                    $order->customer->meta->offsetGet('geslib_id') : null,
            ];
        }

        return response()->xml($pendingOrders, 200, [], 'pedidosPendientes');
    }

    public function show($reference)
    {
        $order = Order::where('reference', $reference)
            ->whereNotNull('placed_at')
            ->with([
                'customer',
                'shippingAddress',
                'billingAddress',
                'productLines',
                'productLines.purchasable',
            ])
            ->first();

        $response = [
            'glmcpedcli' => [],
        ];

        if ($order) {
            $geslibOrder = [
                'codigo' => $order->reference,
                'fecha' => $order->placed_at->format('Y-m-d'),
                'hora' => $order->placed_at->format('H:i:s'),
                'ip_origen' => '127.0.0.1',
                'cod_cliente' => $order->customer->meta->offsetExists('geslib_id') ?
                    $order->customer->meta->offsetGet('geslib_id') : null,
                'n_pedido' => $order->reference,
                'cliente_web' => $order->customer->id,
                'cliente_geslib' => $order->customer->meta->offsetExists('geslib_id') ?
                    $order->customer->meta->offsetGet('geslib_id') : null,
                'fecha_confirmación' => $order->placed_at->format('Y-m-d H:i:s'),
                'gastos_envio' => $order->shipping_total,
                'gastos_contrareembolso' => 0,
                'observaciones' => $order->notes,
                'importe_total' => $order->total,
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
                'calle' => null,
                'num_exterior' => null,
                'num_interior' => null,
                'entre_calle1' => null,
                'entre_calle2' => null,
                'colonia' => null,
                'tlf_oficina' => null,
                'tlf_movil' => null,
                'movil' => null,
                'enviar_factura' => false,
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
                'calle_fac' => null,
                'num_exterior_fac' => null,
                'num_interior_fac' => null,
                'entre_calle1_fac' => null,
                'entre_calle2_fac' => null,
                'colonia_fac' => null,
                'movil_fac' => null,
                'envolver' => null,
                'cod_zona_envio' => 11,
                'zona_envio' => 'España (península)',
                'cod_forma_pago' => 4,
                'forma_pago' => 'Pago con tarjeta',
                'cod_forma_envio' => 4,
                'forma_envio' => 'Envío estándar',
                'referencia_transferencia' => null,
                'numero_tarjeta' => null,
                'numero_trasero_tarjeta' => null,
                'titular' => null,
                'tipo_tarjeta' => null,
                'mes_tarjeta' => null,
                'anio_tarjeta' => null,
                'plazo_entrega' => null,
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
                'horario_entrega' => null,
                'num_modificaciones' => 1,
                'paypal_transaction_id' => null,
                'cfdi' => null,
                'recargo_forma_pago' => null,
                'cod_transportista' => null,
                'nombre_transportista' => null,
                'id_seguimiento' => null,
                'fecha_aviso_seguimiento' => null,
                'recoger_libreria' => 'N',
                'recibo' => null,
                'fac_papel' => null,
                'fac_electronica' => null,
                'login' => null,
                'password' => null,
            ];

            if ($order->shippingAddress) {
                $geslibOrder = array_merge($response['glmcpedcli'], [
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
                $geslibOrder = array_merge($response['glmcpedcli'], [
                    'enviar_factura' => true,
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
                    'calle_fac' => null,
                    'num_exterior_fac' => null,
                    'num_interior_fac' => null,
                    'entre_calle1_fac' => null,
                    'entre_calle2_fac' => null,
                    'colonia_fac' => null,
                    'movil_fac' => null,
                ]);
            }

            $response['glmcpedcli'][] = $geslibOrder;

            foreach ($order->productLines as $line) {
                $response['glmcpedcli'][] = [
                    'cod_linea' => $line->id,
                    'cod_pedido' => $order->reference,
                    'articulo' => $line->purchasable->translateAttribute('geslib-code'),
                    'tipo_articulo' => null,
                    'orden' => null,
                    'cantidad' => $line->quantity,
                    'precio' => null,
                    'descripcion' => $line->purchasable->getDescription(),
                    'isbn' => $line->purchasable->gtin,
                    'ean' => $line->purchasable->ean,
                    'respetar_precio' => 'S',
                    'descuento' => null,
                    'ebook_codigo' => null,
                    'cod_distribuidora' => null,
                    'ebook_formato' => null,
                    'link_portada' => null,
                    'cod_seguridad' => null,
                    'estado_descarga' => null,
                    'link_descarga' => null,
                    'num_pedido_distribuidora' => null,
                    'cancelado' => null,
                    'num_ficheros' => null,
                    'precio_bruto_original' => null,
                    'cupon' => null,
                    'descuento_cupon' => null,
                    'disponibilidad' => null,
                    'dispo_distri' => null,
                    'ebook_drm' => null,
                    'disponibilidad_web' => null,
                    'precio_euros' => null,
                    'pvp_euros' => null,
                    'pvp_brutos' => null,
                ];
            }
        }

        return response()->xml($response, 200, [], 'getPedido');
    }

    public function sync($reference)
    {
        $order = [];

        return response()->xml($order, 200, [], 'getPedido');
    }
}
