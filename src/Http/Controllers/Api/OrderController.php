<?php

namespace NumaxLab\Lunar\Geslib\Http\Controllers\Api;

class OrderController
{
    public function indexPending()
    {
        $pendingOrders = [
            'glmcpedcli' => [
                [
                    'n_pedido' => 103477,
                    'cliente' => 1234,
                ],
                [
                    'n_pedido' => 103478,
                    'cliente' => null,
                ],
            ],
        ];

        return response()->xml($pendingOrders, 200, [], 'pedidosPendientes');
    }

    public function show($code)
    {
        $order = [
            'glmcpedcli' => [
                'codigo' => $code,
                'fecha' => '2025-06-13',
                'hora' => '12:00:00',
                'ip_origen' => '127.0.0.1',
                'cod_cliente' => null,
                'nombre' => 'Cliente de prueba',
                'apellidos' => 'Apellido de prueba',
                'cif' => null,
                'direccion' => 'Calle de prueba, 123',
                'email' => 'email@prueba.com',
                'provincia' => 'A Coruña',
                'localidad' => 'Santiago de Compostela',
                'codigo_postal' => '15701',
                'pais' => 'España',
                'telefono' => '123456789',
                'fax' => null,
                'nombre_fac' => 'Nombre de Facturación',
                'cif_fac' => 'CIF12345678',
                'direccion_fac' => 'Calle de Facturación, 456',
                'email_fac' => 'email@prueba.com',
                'provincia_fac' => 'A Coruña',
                'localidad_fac' => 'Santiago de Compostela',
                'codigo_postal_fac' => '15701',
                'pais_fac' => 'España',
                'telefono_fac' => '987654321',
                'fax_fac' => null,
                'envolver' => null,
                'enviar_factura' => null,
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
                'observaciones' => null,
                'plazo_entrega' => null,
                'gastos_envio' => 0,
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
                'fecha_confirmación' => '2025-06-13 12:00:00',
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
                'n_pedido' => $code,
                'cliente_web' => 12345,
                'cliente_geslib' => null,
                'gastos_contrareembolso' => 0,
                'importe_total' => 3800,
            ],
        ];

        return response()->xml($order, 200, [], 'getPedido');
    }

    public function sync($code)
    {
        $order = [];

        return response()->xml($order, 200, [], 'getPedido');
    }
}
