<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Stateless Shopping Cart</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 850px;
            margin: 40px auto;
            padding: 0 20px;
            background: #f5f7f6;
            color: #163846;
        }

        section {
            background: white;
            padding: 24px;
            margin-bottom: 20px;
            border-radius: 12px;
        }

        button {
            padding: 10px 16px;
            margin: 4px;
            border: 0;
            border-radius: 6px;
            background: #087e80;
            color: white;
            cursor: pointer;
        }

        button:disabled {
            opacity: .5;
            cursor: wait;
        }

        pre {
            padding: 16px;
            background: #eef3f1;
            overflow: auto;
        }
    </style>
</head>
<body>
    <h1>Stateless Shopping Cart</h1>
    <p>Keranjang dikirim pada setiap request dan kosong setelah refresh.</p>

    <noscript>Aktifkan JavaScript untuk menjalankan percobaan ini.</noscript>

    <section>
        <h2>Daftar Produk</h2>

        @foreach ($products as $id => $product)
            <button type="button" onclick="add({{ $id }})">
                {{ $product['name'] }}
                — Rp{{ number_format($product['price'], 0, ',', '.') }}
            </button>
        @endforeach
    </section>

    <section>
        <h2>Keranjang Belanja</h2>

        <div id="items">Keranjang kosong.</div>
        <h3 id="total">Total: Rp0</h3>

        <button type="button" onclick="recalculate()">Hitung Ulang</button>
        <button type="button" onclick="clearCart()">Kosongkan Keranjang</button>
    </section>

    <section>
        <h2>Payload Request</h2>
        <pre id="request">Belum ada request.</pre>

        <h2>Respons Laravel</h2>
        <pre id="response">Belum ada request.</pre>
    </section>

    <script>
        let cart = {};
        let busy = false;

        const addUrl = @json(route('stateless.cart.add'));
        const updateUrl = @json(route('stateless.cart.update'));

        function setBusy(value) {
            busy = value;

            document.querySelectorAll('button').forEach(button => {
                button.disabled = value;
            });
        }

        async function post(url, payload) {
            document.getElementById('request').textContent =
                JSON.stringify(payload, null, 2);

            const response = await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (!response.ok) {
                document.getElementById('response').textContent =
                    JSON.stringify(data, null, 2);

                throw new Error(data.message || 'Request gagal.');
            }

            return data;
        }

        async function add(id) {
            if (busy) return;

            setBusy(true);

            try {
                const data = await post(addUrl, {
                    cart: cart,
                    product_id: id
                });

                applyResponse(data);
            } catch (error) {
                alert(error.message);
            } finally {
                setBusy(false);
            }
        }

        async function recalculate() {
            if (busy) return;

            if (Object.keys(cart).length === 0) {
                alert('Keranjang kosong.');
                return;
            }

            setBusy(true);

            try {
                const data = await post(updateUrl, {
                    cart: cart
                });

                applyResponse(data);
            } catch (error) {
                alert(error.message);
            } finally {
                setBusy(false);
            }
        }

        function applyResponse(data) {
            cart = Object.fromEntries(
                data.items.map(item => [item.id, item.quantity])
            );

            render(data);
        }

        function render(data) {
            const container = document.getElementById('items');
            container.replaceChildren();

            if (data.items.length === 0) {
                container.textContent = 'Keranjang kosong.';
            }

            data.items.forEach(item => {
                const row = document.createElement('p');

                row.textContent =
                    `${item.name} × ${item.quantity} = ` +
                    `Rp${item.subtotal.toLocaleString('id-ID')}`;

                container.appendChild(row);
            });

            document.getElementById('total').textContent =
                'Total: Rp' + data.total.toLocaleString('id-ID');

            document.getElementById('response').textContent =
                JSON.stringify(data, null, 2);
        }

        function clearCart() {
            cart = {};
            render({ items: [], total: 0 });

            document.getElementById('request').textContent =
                'Keranjang dikosongkan di browser tanpa request baru.';
        }
    </script>
</body>
</html>