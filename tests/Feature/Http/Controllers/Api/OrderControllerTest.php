<?php

declare(strict_types=1);

use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\Language;
use Lunar\Models\Order;
use Lunar\Models\OrderAddress;
use Lunar\Models\OrderLine;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

beforeEach(function () {
    Language::factory()->create();
    Currency::factory()->create();
});

function getCredentialsParams(): string
{
    return '?usuario=' . config('lunar.geslib.api.username') . '&clave=' . config('lunar.geslib.api.password');
}

it('complies pending orders xsd', function () {
    $response = $this->get('api/geslib/orders/pending' . getCredentialsParams());

    $dom = new DOMDocument;
    $dom->loadXML($response->getContent());

    expect($dom->schemaValidate(getSchemaPath('Geslib/pending-orders.xsd')))->toBeTrue();

    Order::factory()->count(2)->create([
        'is_geslib' => true,
        'status' => 'payment-received',
        'placed_at' => now()->subHours(5),
    ]);

    $response = $this->get('api/geslib/orders/pending' . getCredentialsParams());

    $dom->loadXML($response->getContent());

    expect($dom->schemaValidate(getSchemaPath('Geslib/pending-orders.xsd')))->toBeTrue();
});

it('lists pending orders', function () {
    $orders = Order::factory()->count(5)->create([
        'is_geslib' => true,
        'status' => 'payment-received',
        'placed_at' => now()->subHours(5),
    ]);

    $response = $this->get('api/geslib/orders/pending' . getCredentialsParams());

    $response
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml');

    $xml = new SimpleXMLElement($response->getContent());

    expect($xml->count())
        ->toBe(5)
        ->and($xml->getName())->toBe('pedidosPendientes')
        ->and((string)$xml->glmcpedcli[0]->n_pedido)->toBe($orders[0]->reference)
        ->and((string)$xml->glmcpedcli[0]->cliente)->toBeEmpty();
});

it('lists pending order with customer', function () {
    $customer = Customer::factory()->create([
        'meta' => [
            'geslib_id' => 'CUST12345',
        ],
    ]);

    $order = Order::factory()->create([
        'is_geslib' => true,
        'status' => 'payment-received',
        'placed_at' => now()->subHours(5),
        'customer_id' => $customer->id,
    ]);

    $response = $this->get('api/geslib/orders/pending' . getCredentialsParams());

    $response
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml');

    $xml = new SimpleXMLElement($response->getContent());

    expect($xml->count())
        ->toBe(1)
        ->and((string)$xml->glmcpedcli[0]->n_pedido)->toBe($order->reference)
        ->and((string)$xml->glmcpedcli[0]->cliente)->toBe('CUST12345');
});

it('returns empty list if no pending orders', function () {
    $response = $this->get('api/geslib/orders/pending' . getCredentialsParams());

    $response
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml');

    $xml = new SimpleXMLElement($response->getContent());

    expect($xml->count())
        ->toBe(0)
        ->and($xml->getName())->toBe('pedidosPendientes');
});

it('complies order xsd', function () {
    $order = Order::factory()->create([
        'is_geslib' => true,
        'status' => 'payment-received',
        'placed_at' => now()->subHours(5),
    ]);

    OrderLine::factory()->create([
        'order_id' => $order->id,
    ]);

    $response = $this->get('api/geslib/orders/' . $order->reference . getCredentialsParams());

    $dom = new DOMDocument;
    $dom->loadXML($response->getContent());

    expect($dom->schemaValidate(getSchemaPath('Geslib/order.xsd')))->toBeTrue();
});

it('gets pending order', function () {
    $customer = Customer::factory()->create();

    $order = Order::factory()->create([
        'is_geslib' => true,
        'status' => 'payment-received',
        'placed_at' => now()->subHours(5),
        'customer_id' => $customer->id,
    ]);

    $response = $this->get('api/geslib/orders/' . $order->reference . getCredentialsParams());

    $response
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml');

    $xml = new SimpleXMLElement($response->getContent());

    expect($xml->getName())
        ->toBe('getPedido')
        ->and((string)$xml->glmcpedcli[0]->codigo)->toBe($order->reference)
        ->and((string)$xml->glmcpedcli[0]->n_pedido)->toBe($order->reference)
        ->and((string)$xml->glmcpedcli[0]->fecha)->toBe($order->placed_at->format('Y-m-d'))
        ->and((string)$xml->glmcpedcli[0]->hora)->toBe($order->placed_at->format('H:i:s'))
        ->and((string)$xml->glmcpedcli[0]->fecha_confirmacion)->toBe($order->placed_at->format('Y-m-d H:i:s'))
        ->and((string)$xml->glmcpedcli[0]->cod_cliente)->toBeEmpty()
        ->and((string)$xml->glmcpedcli[0]->cliente_geslib)->toBeEmpty()
        ->and((string)$xml->glmcpedcli[0]->cliente_web)->toBe((string)$customer->id)
        ->and((string)$xml->glmcpedcli[0]->gastos_envio)->toBe((string)$order->shipping_total)
        ->and((string)$xml->glmcpedcli[0]->observaciones)->toBe((string)$order->notes)
        ->and((string)$xml->glmcpedcli[0]->importe_total)->toBe((string)$order->total);
});

it('gets pending order with customer geslib code and shipping address', function () {
    $customer = Customer::factory()->create([
        'meta' => [
            'geslib_id' => 'CUST12345',
        ],
    ]);

    $order = Order::factory()->create([
        'is_geslib' => true,
        'status' => 'payment-received',
        'placed_at' => now()->subHours(5),
        'customer_id' => $customer->id,
    ]);

    $country = Country::factory()->create();

    $shippingAddress = OrderAddress::factory()->create([
        'type' => 'shipping',
        'order_id' => $order->id,
        'country_id' => $country->id,
    ]);

    $response = $this->get('api/geslib/orders/' . $order->reference . getCredentialsParams());

    $response
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml');

    $xml = new SimpleXMLElement($response->getContent());

    expect($xml->getName())
        ->toBe('getPedido')
        ->and((string)$xml->glmcpedcli[0]->cod_cliente)->toBe('CUST12345')
        ->and((string)$xml->glmcpedcli[0]->cliente_geslib)->toBe('CUST12345')
        ->and((string)$xml->glmcpedcli[0]->nombre)->toBe($shippingAddress->first_name)
        ->and((string)$xml->glmcpedcli[0]->apellidos)->toBe($shippingAddress->last_name)
        ->and((string)$xml->glmcpedcli[0]->cif)->toBe((string)$shippingAddress->tax_identifier)
        ->and((string)$xml->glmcpedcli[0]->direccion)->toBe($shippingAddress->line_one . ' ' . $shippingAddress->line_two . ' ' . $shippingAddress->line_three)
        ->and((string)$xml->glmcpedcli[0]->email)->toBe((string)$shippingAddress->contact_email)
        ->and((string)$xml->glmcpedcli[0]->provincia)->toBe((string)$shippingAddress->state)
        ->and((string)$xml->glmcpedcli[0]->localidad)->toBe((string)$shippingAddress->city)
        ->and((string)$xml->glmcpedcli[0]->codigo_postal)->toBe((string)$shippingAddress->postcode)
        ->and((string)$xml->glmcpedcli[0]->pais)->toBe((string)$country->name)
        ->and((string)$xml->glmcpedcli[0]->telefono)->toBe((string)$shippingAddress->contact_phone);
});

it('gets pending order with billing address', function () {
    $customer = Customer::factory()->create();

    $order = Order::factory()->create([
        'is_geslib' => true,
        'status' => 'payment-received',
        'placed_at' => now()->subHours(5),
        'customer_id' => $customer->id,
    ]);

    $country = Country::factory()->create();

    $billingAddress = OrderAddress::factory()->create([
        'type' => 'billing',
        'order_id' => $order->id,
        'country_id' => $country->id,
    ]);

    $response = $this->get('api/geslib/orders/' . $order->reference . getCredentialsParams());

    $response
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml');

    $xml = new SimpleXMLElement($response->getContent());

    expect($xml->getName())
        ->toBe('getPedido')
        ->and((string)$xml->glmcpedcli[0]->enviar_factura)->toBe('1')
        ->and((string)$xml->glmcpedcli[0]->nombre_fac)->toBe($billingAddress->full_name)
        ->and((string)$xml->glmcpedcli[0]->cif_fac)->toBe((string)$billingAddress->tax_identifier)
        ->and((string)$xml->glmcpedcli[0]->direccion_fac)->toBe($billingAddress->line_one . ' ' . $billingAddress->line_two . ' ' . $billingAddress->line_three)
        ->and((string)$xml->glmcpedcli[0]->email_fac)->toBe((string)$billingAddress->contact_email)
        ->and((string)$xml->glmcpedcli[0]->provincia_fac)->toBe((string)$billingAddress->state)
        ->and((string)$xml->glmcpedcli[0]->localidad_fac)->toBe((string)$billingAddress->city)
        ->and((string)$xml->glmcpedcli[0]->codigo_postal_fac)->toBe((string)$billingAddress->postcode)
        ->and((string)$xml->glmcpedcli[0]->pais_fac)->toBe((string)$country->name)
        ->and((string)$xml->glmcpedcli[0]->telefono_fac)->toBe((string)$billingAddress->contact_phone);
});

it('does not list already synced orders as pending', function () {
    Order::factory()->count(3)->create([
        'is_geslib' => true,
        'status' => 'payment-received',
        'placed_at' => now()->subHours(5),
        'synced_with_geslib_at' => now()->subHour(),
    ]);

    Order::factory()->count(2)->create([
        'is_geslib' => true,
        'status' => 'payment-received',
        'placed_at' => now()->subHours(5),
    ]);

    $response = $this->get('api/geslib/orders/pending' . getCredentialsParams());

    $response->assertOk();

    $xml = new SimpleXMLElement($response->getContent());

    expect($xml->count())->toBe(2);
});

it('syncs an order and returns its detail', function () {
    $customer = Customer::factory()->create();

    $order = Order::factory()->create([
        'is_geslib' => true,
        'status' => 'payment-received',
        'placed_at' => now()->subHours(5),
        'customer_id' => $customer->id,
        'synced_with_geslib_at' => null,
    ]);

    $response = $this->get('api/geslib/orders/' . $order->reference . '/sync' . getCredentialsParams());

    $response
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml');

    $xml = new SimpleXMLElement($response->getContent());

    expect($xml->getName())
        ->toBe('getPedido')
        ->and((string)$xml->glmcpedcli[0]->codigo)->toBe($order->reference)
        ->and((string)$xml->glmcpedcli[0]->n_pedido)->toBe($order->reference);

    $this->assertDatabaseMissing($order->getTable(), [
        'id' => $order->id,
        'synced_with_geslib_at' => null,
    ]);
});

it('synced order no longer appears in pending list', function () {
    $order = Order::factory()->create([
        'is_geslib' => true,
        'status' => 'payment-received',
        'placed_at' => now()->subHours(5),
    ]);

    $this
        ->get('api/geslib/orders/pending' . getCredentialsParams())
        ->assertOk();

    $xml = new SimpleXMLElement(
        $this->get('api/geslib/orders/pending' . getCredentialsParams())->getContent(),
    );
    expect($xml->count())->toBe(1);

    $this
        ->get('api/geslib/orders/' . $order->reference . '/sync' . getCredentialsParams())
        ->assertOk();

    $xml = new SimpleXMLElement(
        $this->get('api/geslib/orders/pending' . getCredentialsParams())->getContent(),
    );
    expect($xml->count())->toBe(0);
});

it('returns empty response when syncing a non-existent order', function () {
    $response = $this->get('api/geslib/orders/NON-EXISTENT/sync' . getCredentialsParams());

    $response
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml');

    $xml = new SimpleXMLElement($response->getContent());

    expect($xml->getName())
        ->toBe('getPedido')
        ->and($xml->count())->toBe(0);
});

it('gets pending order with lines', function () {
    $customer = Customer::factory()->create();

    $order = Order::factory()->create([
        'is_geslib' => true,
        'status' => 'payment-received',
        'placed_at' => now()->subHours(5),
        'customer_id' => $customer->id,
    ]);

    $geslibIds = [12345, 67890];
    $lines = [];

    $typeId = ensureCollectionsForArticle(typeId: 'L')[0];

    foreach ($geslibIds as $geslibId) {
        $product = Product::factory()->create();

        $product->collections()->attach($typeId);

        $variant = ProductVariant::factory()->for($product)->create([
            'sku' => $geslibId,
        ]);

        $lines[] = OrderLine::factory()->create([
            'order_id' => $order->id,
            'purchasable_id' => $variant->id,
        ]);
    }

    $response = $this->get('api/geslib/orders/' . $order->reference . getCredentialsParams());

    $response
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml');

    $xml = new SimpleXMLElement($response->getContent());

    expect($xml->getName())
        ->toBe('getPedido')
        ->and($xml->glmlpedcli->count())->toBe(2)
        ->and((string)$xml->glmlpedcli[0]->cod_linea)->toBe((string)$lines[0]->id)
        ->and((string)$xml->glmlpedcli[0]->cod_pedido)->toBe($order->reference)
        ->and((string)$xml->glmlpedcli[0]->articulo)->toBe((string)$lines[0]->purchasable->sku)
        ->and((string)$xml->glmlpedcli[0]->tipo_articulo)->toBe('L')
        ->and((string)$xml->glmlpedcli[0]->orden)->toBe('1')
        ->and((string)$xml->glmlpedcli[0]->cantidad)->toBe((string)$lines[0]->quantity)
        ->and((string)$xml->glmlpedcli[0]->precio)->toBe((string)$lines[0]->unit_price->decimal())
        ->and((string)$xml->glmlpedcli[0]->descripcion)->toBe((string)$lines[0]->purchasable->getDescription())
        ->and((string)$xml->glmlpedcli[0]->isbn)->toBe((string)$lines[0]->purchasable->gtin)
        ->and((string)$xml->glmlpedcli[0]->ean)->toBe((string)$lines[0]->purchasable->ean)
        ->and((string)$xml->glmlpedcli[0]->respetar_precio)->toBe('S')
        ->and((string)$xml->glmlpedcli[0]->descuento)->toBe((string)$lines[0]->discount_total->decimal())
        ->and((string)$xml->glmlpedcli[0]->cancelado)->toBe('N')
        ->and((string)$xml->glmlpedcli[0]->precio_euros)->toBe((string)$lines[0]->unit_price)
        ->and((string)$xml->glmlpedcli[0]->pvp_euros)->toBe((string)$lines[0]->unit_price)
        ->and((string)$xml->glmlpedcli[0]->pvp_bruto)->toBe((string)$lines[0]->sub_total->decimal());
});
