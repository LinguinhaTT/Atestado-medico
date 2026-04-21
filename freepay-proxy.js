/**
 * FreePay Brasil — Proxy Node.js (Express)
 * npm install express node-fetch cors
 * node freepay-proxy.js
 */

const express  = require('express');
const fetch    = require('node-fetch');
const cors     = require('cors');
const app      = express();

// ═══ SUAS CREDENCIAIS ════════════════════════════════════════════
const PUBLIC_KEY  = 'freepay_live_4goIIsfQXV13wkEX95fIbpe9eIhETIfN';
const SECRET_KEY  = 'sk_live_B6wTubWqdBiZdKo1vvZA2bT3UFeTrGcg';
const CREATE_URL  = 'https://api.freepaybrasil.com/v1/payment-transaction/create';
const STATUS_URL  = 'https://api.freepaybrasil.com/v1/payment-transaction';
// ═════════════════════════════════════════════════════════════════

const AUTH = 'Basic ' + Buffer.from(`${PUBLIC_KEY}:${SECRET_KEY}`).toString('base64');

app.use(cors()); // em produção: cors({ origin: 'https://atestadomedico.org' })
app.use(express.json());

// ─── CRIAR TRANSAÇÃO ────────────────────────────────────────────
app.post('/freepay/create', async (req, res) => {
    try {
        const response = await fetch(CREATE_URL, {
            method: 'POST',
            headers: {
                'Content-Type':  'application/json',
                'Authorization': AUTH,
            },
            body: JSON.stringify(req.body),
        });
        const data = await response.json();
        res.status(response.status).json(data);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// ─── CONSULTAR STATUS ────────────────────────────────────────────
app.get('/freepay/status/:id', async (req, res) => {
    try {
        const response = await fetch(`${STATUS_URL}/${req.params.id}`, {
            headers: { 'Authorization': AUTH },
        });
        const data = await response.json();
        res.status(response.status).json(data);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.listen(3000, () => console.log('✅ FreePay proxy rodando em http://localhost:3000'));
