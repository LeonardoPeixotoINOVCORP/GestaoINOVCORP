<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
 
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1f2937;
            padding: 36px 42px;
            line-height: 1.5;
            background: #ffffff;
        }
 
        /* Cabeçalho */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 24px;
        }
 
        .header-logo {
            display: table-cell;
            width: 30%;
            vertical-align: top;
        }
 
        .header-logo img {
            height: 80px;
            object-fit: contain;
        }
 
        .header-logo .empresa-nome-text {
            font-size: 18px;
            font-weight: bold;
            color: #111827;
            margin-top: 4px;
        }
 
        .header-cliente {
            display: table-cell;
            width: 40%;
            vertical-align: top;
            padding-left: 20px;
            padding-top: 10px;
        }
 
        .header-cliente .cliente-nome-topo {
            font-size: 12px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 4px;
        }
 
        .header-cliente .cliente-info-topo {
            font-size: 10px;
            color: #374151;
            line-height: 1.7;
        }
 
        .header-doc {
            display: table-cell;
            width: 30%;
            text-align: right;
            vertical-align: top;
            padding-top: 4px;
        }
 
        .header-doc h1 {
            font-size: 20px;
            font-weight: bold;
            color: #111827;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
 
        .header-doc .doc-numero {
            font-size: 13px;
            color: #374151;
            margin-top: 4px;
        }
 
        /* Linha cliente / contribuinte */
        .meta-bar {
            display: table;
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            margin-bottom: 20px;
        }
 
        .meta-cell {
            display: table-cell;
            padding: 8px 14px;
            border-right: 1px solid #d1d5db;
            vertical-align: middle;
        }
 
        .meta-cell:last-child {
            border-right: none;
        }
 
        .meta-cell.right {
            text-align: right;
        }
 
        .meta-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: .5px;
            margin-bottom: 2px;
        }
 
        .meta-value {
            font-size: 11px;
            font-weight: bold;
            color: #111827;
        }
 
        /* Titulo do pedido */
        .pedido-titulo {
            background: #f1f5f9;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            color: #111827;
            padding: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0;
        }
 
        /* Tabela artigos */
        table.artigos {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
 
        table.artigos thead tr {
            background: #f8fafc;
        }
 
        table.artigos th {
            text-align: left;
            padding: 8px 10px;
            font-size: 9px;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: .5px;
            border-bottom: 1px solid #e5e7eb;
            border-top: 1px solid #e5e7eb;
        }
 
        table.artigos td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 10px;
            vertical-align: top;
        }
 
        table.artigos .grupo-titulo td {
            background: #f8fafc;
            font-weight: bold;
            font-size: 11px;
            color: #111827;
            padding: 8px 10px;
            text-align: right;
        }
 
        table.artigos .grupo-titulo td:first-child {
            text-align: left;
        }
 
        table.artigos .descricao-extra td {
            padding-top: 0;
            padding-bottom: 10px;
            font-size: 10px;
            color: #374151;
            line-height: 1.6;
            border-bottom: 1px solid #e5e7eb;
        }
 
        .text-right { text-align: right; }
        .referencia { font-family: monospace; color: #6b7280; font-size: 9px; }
        .bold { font-weight: bold; }
 
        /* Secção inferior: termos + totais */
        .bottom-section {
            display: table;
            width: 100%;
            margin-top: 28px;
        }
 
        .termos-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 24px;
        }
 
        .totais-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
 
        .secao-titulo {
            font-size: 10px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 4px;
        }
 
        .termos-linha {
            display: table;
            width: 100%;
            font-size: 10px;
            margin-bottom: 3px;
        }
 
        .termos-key {
            display: table-cell;
            color: #6b7280;
            width: 45%;
        }
 
        .termos-val {
            display: table-cell;
            color: #111827;
            font-weight: bold;
        }
 
        .termos-nota {
            font-size: 9px;
            color: #374151;
            margin-top: 6px;
            line-height: 1.6;
        }
 
        .ibans {
            font-size: 9px;
            color: #374151;
            margin-top: 6px;
            line-height: 1.7;
        }
 
        /* totais table */
        table.totais {
            width: 100%;
            border-collapse: collapse;
        }
 
        table.totais td {
            padding: 5px 4px;
            font-size: 11px;
            border: none;
        }
 
        table.totais .label-col {
            color: #374151;
        }
 
        table.totais .val-col {
            text-align: right;
            color: #111827;
        }
 
        table.totais tr.total-final td {
            font-size: 13px;
            font-weight: bold;
            border-top: 2px solid #111827;
            padding-top: 8px;
            color: #111827;
        }
 
        .nao-fatura {
            font-size: 9px;
            color: #6b7280;
            text-align: right;
            margin-top: 6px;
        }
 
        /* Multibanco */
        .mb-box {
            display: table;
            width: 100%;
            margin-top: 14px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 10px;
        }
 
        .mb-logo-cell {
            display: table-cell;
            width: 60px;
            vertical-align: middle;
        }
 
        .mb-logo {
            background: #003087;
            color: white;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            padding: 6px 4px;
            border-radius: 4px;
            letter-spacing: .5px;
            width: 46px;
        }
 
        .mb-logo span {
            display: block;
            font-size: 7px;
            font-weight: normal;
            margin-top: 2px;
            letter-spacing: 0;
        }
 
        .mb-dados-cell {
            display: table-cell;
            vertical-align: middle;
            font-size: 10px;
            line-height: 1.8;
        }
 
        .mb-dados-cell .mb-row {
            display: table;
            width: 100%;
        }
 
        .mb-key {
            display: table-cell;
            color: #6b7280;
            width: 80px;
        }
 
        .mb-val {
            display: table-cell;
            font-weight: bold;
            color: #111827;
        }
 
        /* Footer */
        .footer {
            margin-top: 50px;
            padding-top: 14px;
            border-top: 1px solid #e5e7eb;
            display: table;
            width: 100%;
        }
 
        .footer-left {
            display: table-cell;
            width: 50%;
            vertical-align: bottom;
        }
 
        .footer-contactos-titulo {
            font-size: 10px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 6px;
        }
 
        .footer-contactos {
            font-size: 9px;
            color: #6b7280;
            line-height: 1.8;
        }
 
        .footer-right {
            display: table-cell;
            width: 50%;
            text-align: right;
            vertical-align: bottom;
            font-size: 9px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
 
    <!-- Header -->
    <div class="header">
        <div class="header-logo">
            @if(!empty($empresa['logotipo']))
                <img src="{{ $empresa['logotipo'] }}" />
            @else
                <div class="empresa-nome-text">
                    {{ $empresa['nome'] ?? config('app.name') }}
                </div>
            @endif

            @if(!empty($empresa['logotipo']))
                <div class="empresa-nome-text"
                    style="font-size:13px; margin-top:6px; text-transform:uppercase; letter-spacing:1px;">
                    {{ $empresa['nome'] ?? config('app.name') }}
                </div>

                <div style="font-size:9px; color:#9ca3af; text-transform:uppercase; letter-spacing:2px;">
                    {{ $empresa['subtitulo'] ?? '' }}
                </div>
            @endif
        </div>
 
        <div class="header-cliente">
            <div class="cliente-nome-topo">{{ $documento->entidade->nome }}</div>
            <div class="cliente-info-topo">
                {{ $documento->entidade->morada ?? '' }}<br>
                @if($documento->entidade->localidade)
                    {{ $documento->entidade->localidade }}<br>
                @endif
                @if($documento->entidade->codigo_postal)
                    {{ $documento->entidade->codigo_postal }} {{ $documento->entidade->localidade }}
                @endif
            </div>
        </div>
 
        <div class="header-doc">
            <h1>{{ $tipo }}</h1>
            <div class="doc-numero">{{ $documento->numero }}/{{ \Carbon\Carbon::now()->year }}</div>
        </div>
    </div>
 
    <!-- Barra meta: cliente nº, contribuinte, edição, data -->
    <div class="meta-bar">
        <div class="meta-cell" style="width:18%;">
            <div class="meta-label">Cliente Nº</div>
            <div class="meta-value">{{ $documento->entidade->numero ?? '—' }}</div>
        </div>
        <div class="meta-cell" style="width:28%;">
            <div class="meta-label">Contribuinte</div>
            <div class="meta-value">{{ $documento->entidade->nif }}</div>
        </div>
        <div class="meta-cell right" style="width:14%;">
            <div class="meta-label">Edição</div>
            <div class="meta-value">{{ $documento->edicao ?? 1 }}</div>
        </div>
        <div class="meta-cell right" style="width:6%; border-right:none;">
            <div class="meta-label">de</div>
            <div class="meta-value">
                @if($documento->data_proposta ?? $documento->data_encomenda)
                    {{ \Carbon\Carbon::parse($documento->data_proposta ?? $documento->data_encomenda)->format('d M Y') }}
                @endif
            </div>
        </div>
    </div>
 
    <!-- Titulo do pedido -->
    <div class="pedido-titulo">Pedido #</div>
 
    <!-- Tabela de artigos -->
    <table class="artigos">
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th>Descrição</th>
                <th class="text-right" style="width:8%;">Qtd</th>
                <th class="text-right" style="width:6%;">Un</th>
                <th class="text-right" style="width:8%;">Desc.</th>
                <th class="text-right" style="width:10%;">Preço Unit.</th>
                <th class="text-right" style="width:10%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $grupoIdx = 0; @endphp
            @foreach($documento->grupos ?? [] as $grupo)
                @php $grupoIdx++; @endphp
                <!-- Linha grupo / serviço -->
                <tr class="grupo-titulo">
                    <td>{{ $grupoIdx }}. {{ strtoupper($grupo['tipo'] ?? 'SERVIÇO') }}</td>
                    <td colspan="5"></td>
                    <td>{{ number_format($grupo['total'] ?? 0, 2, ',', '.') }} €</td>
                </tr>
                @foreach($grupo['linhas'] ?? [] as $i => $linha)
                <tr>
                    <td class="referencia">{{ $grupoIdx }}_{{ str_pad($i+1, 2, '0', STR_PAD_LEFT) }} | {{ $linha->artigo->referencia ?? '' }}</td>
                    <td class="bold">{{ $linha->artigo->nome }}</td>
                    <td class="text-right">{{ number_format($linha->quantidade, 2, ',', '.') }}</td>
                    <td class="text-right">{{ $linha->artigo->unidade ?? 'Un' }}</td>
                    <td class="text-right">{{ number_format($linha->desconto ?? 0, 2, ',', '.') }} %</td>
                    <td class="text-right">{{ number_format($linha->preco_venda, 2, ',', '.') }} €</td>
                    <td class="text-right">{{ number_format($linha->quantidade * $linha->preco_venda * (1 - ($linha->desconto ?? 0)/100), 2, ',', '.') }} €</td>
                </tr>
                @if(!empty($linha->descricao_extra))
                <tr class="descricao-extra">
                    <td></td>
                    <td colspan="6">{{ $linha->descricao_extra }}</td>
                </tr>
                @endif
                @endforeach
            @endforeach
 
            {{-- Fallback: linhas simples sem grupos --}}
            @if(empty($documento->grupos))
                @foreach($documento->linhas as $i => $linha)
                <tr>
                    <td class="referencia">{{ $i+1 }}_01 | {{ $linha->artigo->referencia ?? '' }}</td>
                    <td class="bold">{{ $linha->artigo->nome }}</td>
                    <td class="text-right">{{ number_format($linha->quantidade, 2, ',', '.') }}</td>
                    <td class="text-right">{{ $linha->artigo->unidade ?? 'Un' }}</td>
                    <td class="text-right">{{ number_format($linha->desconto ?? 0, 2, ',', '.') }} %</td>
                    <td class="text-right">{{ number_format($linha->preco_venda, 2, ',', '.') }} €</td>
                    <td class="text-right">{{ number_format($linha->quantidade * $linha->preco_venda * (1 - ($linha->desconto ?? 0)/100), 2, ',', '.') }} €</td>
                </tr>
                @if(!empty($linha->descricao_extra))
                <tr class="descricao-extra">
                    <td></td>
                    <td colspan="6">{{ $linha->descricao_extra }}</td>
                </tr>
                @endif
                @endforeach
            @endif
        </tbody>
    </table>
 
    <!-- Secção Inferior: Termos + Totais -->
    @php
        $subtotal   = $documento->linhas->sum(fn($l) => $l->quantidade * $l->preco_venda * (1 - ($l->desconto ?? 0)/100));
        $descontoL  = $documento->linhas->sum(fn($l) => $l->quantidade * $l->preco_venda * (($l->desconto ?? 0)/100));
        $descontoG  = $documento->desconto_geral ?? 0;
        $baseIva    = $subtotal * (1 - $descontoG/100);
        $totalIva   = $documento->linhas->sum(fn($l) => $l->quantidade * $l->preco_venda * (1 - ($l->desconto ?? 0)/100) * ($l->iva / 100)) * (1 - $descontoG/100);
        $total      = $baseIva + $totalIva;
        $taxaIva    = $documento->linhas->first()?->iva ?? 23;
    @endphp
 
    <div class="bottom-section">
        <!-- Termos -->
        <div class="termos-col">
            <div class="secao-titulo">Termos e Condições</div>
 
            <div class="termos-linha">
                <span class="termos-key">Prazo Entrega</span>
                <span class="termos-val">{{ $documento->prazo_entrega ?? '30 DIAS' }}</span>
            </div>
            <div style="font-size:10px; color:#374151; margin:4px 0 2px;">Condições de Pagamento:</div>
            <div class="termos-linha" style="padding-left:8px;">
                <span class="termos-key">- Adjudicação</span>
                <span class="termos-val">{{ $documento->pagamento_adjudicacao ?? '50,00' }} %</span>
            </div>
            <div class="termos-linha" style="padding-left:8px;">
                <span class="termos-key">- Conclusão</span>
                <span class="termos-val">{{ $documento->pagamento_conclusao ?? '50,00' }} %</span>
            </div>
 
            @if($documento->validade)
            <div class="termos-linha" style="margin-top:4px;">
                <span class="termos-key">Válido até</span>
                <span class="termos-val">{{ \Carbon\Carbon::parse($documento->validade)->format('d/m/Y') }}</span>
            </div>
            @endif
 
            <div class="termos-nota">*Valor sem IVA incluído</div>
 
            <div class="ibans">
                @foreach($documento->ibans ?? [] as $iban)
                    IBAN {{ $iban['numero'] }} ({{ $iban['banco'] }})<br>
                @endforeach
                @if(empty($documento->ibans))
                    IBAN PT50 0033 0000 00117871879 05 (MillenniumBcp)<br>
                    IBAN PT50 0269 0660 00209630911 32 (Bankinter)
                @endif
            </div>
        </div>
 
        <!-- Totals -->
        <div class="totais-col">
            <table class="totais">
                <tr>
                    <td class="label-col">Subtotal</td>
                    <td class="val-col">{{ number_format($subtotal + $descontoL, 2, ',', '.') }} €</td>
                </tr>
                <tr>
                    <td class="label-col">Desconto Linha</td>
                    <td class="val-col">{{ number_format($descontoL, 2, ',', '.') }} €</td>
                </tr>
                <tr>
                    <td class="label-col">Desconto Geral</td>
                    <td class="val-col">
                        {{ number_format($descontoG, 2, ',', '.') }} %&nbsp;&nbsp;&nbsp;
                        {{ number_format($subtotal * $descontoG / 100, 2, ',', '.') }} €
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Total sem IVA</td>
                    <td class="val-col">{{ number_format($baseIva, 2, ',', '.') }} €</td>
                </tr>
                <tr>
                    <td class="label-col">IVA</td>
                    <td class="val-col">
                        {{ number_format($taxaIva, 2, ',', '.') }} %&nbsp;&nbsp;&nbsp;
                        {{ number_format($totalIva, 2, ',', '.') }} €
                    </td>
                </tr>
                <tr class="total-final">
                    <td class="label-col">Total com IVA</td>
                    <td class="val-col">{{ number_format($total, 2, ',', '.') }} €</td>
                </tr>
            </table>
 
            <div class="nao-fatura">Este documento não serve de fatura</div>
 
            <!-- Multibanco -->
            @if($documento->mb_entidade ?? false)
            <div class="mb-box">
                <div class="mb-logo-cell">
                    <div class="mb-logo">MB<span>MULTIBANCO</span></div>
                </div>
                <div class="mb-dados-cell">
                    <div class="mb-row">
                        <span class="mb-key">Entidade</span>
                        <span class="mb-val">{{ $documento->mb_entidade }}</span>
                    </div>
                    <div class="mb-row">
                        <span class="mb-key">Referencia</span>
                        <span class="mb-val">{{ $documento->mb_referencia }}</span>
                    </div>
                    <div class="mb-row">
                        <span class="mb-key">Valor</span>
                        <span class="mb-val">{{ number_format($documento->mb_percentagem ?? 50, 2, ',', '.') }} %&nbsp;&nbsp;{{ number_format($total * ($documento->mb_percentagem ?? 50) / 100, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
 
    <!-- Footer -->
    <div class="footer">
        <div class="footer-left">
            <div class="footer-contactos-titulo">Contactos</div>
            <div class="footer-contactos">
                {{ $empresa['morada'] ?? '' }} {{ $empresa['codigo_postal'] ?? '' }} {{ $empresa['localidade'] ?? '' }}<br>
                @if($empresa['telefone'] ?? false)
                    T {{ $empresa['telefone'] }} (chamada para a rede fixa nacional)<br>
                @endif
                @if($empresa['telemovel'] ?? false)
                    M {{ $empresa['telemovel'] }} (chamada para a rede móvel nacional)<br>
                @endif
                @if($empresa['email'] ?? false)
                    {{ $empresa['email'] }}<br>
                @endif
                @if($empresa['website'] ?? false)
                    {{ $empresa['website'] }}
                @endif
            </div>
        </div>
        <div class="footer-right">
            @if($empresa['logo_rodape'] ?? false)
                <img src="{{ $empresa['logo_rodape'] }}" style="height:40px; object-fit:contain;" /><br>
            @endif
            Pág 1 de 1
        </div>
    </div>
 
</body>
</html>