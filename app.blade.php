<!doctype html>
<html lang="de">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Dokument</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: { sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui'] },
                        colors: {
                            secondary: { DEFAULT: 'hsl(0 0% 92.1%)', foreground: 'hsl(0 0% 9%)' },
                        },
                    },
                },
            };
        </script>
        <style>
            @page {
                size: A4;
                margin: 0;
            }
            @media print {
                body {
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                    margin: 0;
                    padding: 0;
                }
                .invoice-container {
                    box-shadow: none !important;
                    margin: 0 !important;
                    max-width: 100% !important;
                    width: 210mm;
                    page-break-after: avoid;
                }
                .no-print {
                    display: none !important;
                }
            }
            @media screen {
                .invoice-container {
                    width: 210mm;
                    min-height: 297mm;
                }
            }
        </style>
    </head>
    <body class="bg-slate-100 font-sans antialiased">
        <div id="document-content" class="invoice-container mx-auto my-8 bg-white bg-contain shadow-xl print:my-0 print:shadow-none">
            <div class="flex h-full items-center justify-center p-20 text-gray-500">
                Lade Daten (data.json & private.json)...<br />
                (Bitte "Live Server" nutzen)
            </div>
        </div>

        <div class="no-print mx-auto max-w-4xl py-6 text-center">
            <button
                onclick="window.print()"
                class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-6 py-3 text-sm font-semibold text-secondary shadow-lg hover:bg-slate-800"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
                    />
                </svg>
                Drucken
            </button>
        </div>

        <script>
            const formatMoney = (amount) => {
                return new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount);
            };

            function renderDocument(data, profile) {
                // 1. CONFIGURATION (Switch Logic)
                const isInvoice = data.type === 'invoice';
                const config = isInvoice
                    ? {
                          title: 'RECHNUNG',
                          idLabel: 'Rechnungsnummer',
                          idPrefix: '',
                          dateLabel: 'Rechnungsdatum',
                          validLabel: 'Fälligkeitsdatum',
                      }
                    : {
                          title: 'ANGEBOT',
                          idLabel: 'Angebotsnummer',
                          idPrefix: 'A-',
                          dateLabel: 'Angebotsdatum',
                          validLabel: 'Gültig bis',
                      };

                // 2. CALCULATIONS
                let subTotal = 0;
                const rowsHtml = data.items
                    .map((item) => {
                        const rowTotal = item.qty * item.price;
                        subTotal += rowTotal;
                        return `
            <tr class="border-b">
                <td class="px-3 py-3 align-top">${item.qty}x</td>
                <td class="px-3 py-3 align-top">
                    <p class="font-semibold">${item.title}</p>
                    <p class="mt-1 text-xs text-gray-600">${item.desc || ''}</p>
                    ${item.subtitle ? `<p class="mt-1 text-xs text-gray-400 italic">${item.subtitle}</p>` : ''}
                </td>
                <td class="px-3 py-3 text-right align-top font-medium">€${formatMoney(item.price)}</td>
                <td class="px-3 py-3 text-right align-top font-semibold">€${formatMoney(rowTotal)}</td>
            </tr>`;
                    })
                    .join('');

                const grandTotal = subTotal;
                const paidAmount = data.payment && data.payment.amount_paid ? data.payment.amount_paid : 0;
                const openAmount = grandTotal - paidAmount;
                const isPaid = openAmount <= 0.01;

                // 3. DYNAMIC HTML BLOCKS

                // Block A: Status Badge (Only for Invoice)
                const statusBadgeHtml = isInvoice
                    ? `
            <div class="mb-1.5 flex justify-between border-b border-slate-200">
                <span class="font-medium">Status:</span>
                ${isPaid ? '<span class="font-semibold text-green-500">BEZAHLT</span>' : '<span class="font-semibold text-red-600">OFFEN</span>'}
            </div>`
                    : '';

                // Block B: Totals Section
                const totalsHtml = `
            <div class="flex justify-between py-1.5">
                <span class="">Zwischensumme:</span>
                <span class="font-medium">€${formatMoney(subTotal)}</span>
            </div>
            <div class="mb-1.5 flex justify-between border-b border-slate-200">
                <span class="">USt. (0%):</span>
                <span class="font-medium">€0,00</span>
            </div>
            
            ${
                isInvoice
                    ? `
                <div class="flex justify-between py-1.5 font-semibold">
                    <span class="">Rechnungsbetrag:</span>
                    <span class="">€${formatMoney(grandTotal)}</span>
                </div>
                ${
                    paidAmount > 0
                        ? `
                <div class="flex justify-between py-1.5">
                    <span class="">Bereits bezahlt:</span>
                    <span class="font-medium text-green-500">(€${formatMoney(paidAmount)})</span>
                </div>`
                        : ''
                }
                <div class="flex justify-between rounded-lg text-secondary">
                    <span class="text-base font-bold">OFFENER BETRAG:</span>
                    <span class="text-xl font-bold ${isPaid ? 'text-green-500' : 'text-red-600'}">€${formatMoney(openAmount > 0 ? openAmount : 0)}</span>
                </div>
            `
                    : `
                <div class="gpa-3 flex items-center justify-between rounded-lg text-secondary">
                    <span class="text-base font-bold">GESAMTBETRAG: &nbsp;</span>
                    <span class="text-xl font-bold">€${formatMoney(grandTotal)}</span>
                </div>
            `
            }
        `;

                // Block: AGB Note (Only for Offers)
                const agbNoteHtml = !isInvoice
                    ? `
            <div class="mb-6 text-center text-xs italic text-slate-300">
              Es gelten der beigefügte Projektvertrag sowie unsere Allgemeinen Geschäftsbedingungen (AGB).
            </div>
            `
                    : '';

                // Block C: Bottom Action Area (Payment Box OR Signature)
                let bottomActionHtml = '';

                if (isInvoice) {
                    // INVOICE: Show Payment Status Box
                    if (isPaid) {
                        bottomActionHtml = `
                <div class="mb-6 rounded-lg border-l-4 border-green-500 bg-green-50 p-4">
                    <p class="font-semibold text-green-800">Zahlung bestätigt</p>
                    <p class="mt-1 text-sm text-green-700">
                        ${data.payment.confirmed_text || 'Der Betrag wurde dankend erhalten.'}
                    </p>
                </div>`;
                    } else {
                        bottomActionHtml = `
                <div class="mb-6 rounded-lg border-l-4 border-red-500 bg-red-50 p-4">
                    <p class="font-semibold text-red-900">Zahlungsaufforderung</p>
                    <p class="mt-1 text-sm text-red-800">
                        Bitte überweisen Sie den offenen Betrag von <strong class="text-red-900">€${formatMoney(openAmount)}</strong> bis zum
                        <strong class="text-red-900">${data.valid_until}</strong> auf das unten angegebene Bankkonto.
                    </p>
                </div>`;
                    }
                } else {
                    // OFFER: Show Signature Lines
                    bottomActionHtml = `
            <div class="mb-6 rounded-lg border border-secondary/20 bg-secondary/10 p-4 text-secondary backdrop-blur-sm">
                <h3 class="mb-3 text-lg font-semibold">Unterschrift zur Genehmigung</h3>
                <div class="mt-10 grid grid-cols-2 gap-8">
                    <div>
                        <div class="border-b border-secondary/50 pb-2"></div>
                        <p class="mt-2 text-xs">Ort, Datum</p>
                    </div>
                    <div>
                        <div class="border-b border-secondary/50 pb-2"></div>
                        <p class="mt-2 text-xs">Unterschrift (${data.customer.name})</p>
                    </div>
                </div>
            </div>`;
                }

                // Set Background
                document.getElementById('document-content').style.backgroundImage = `url('${profile.company.background_url}')`;

                // -- RENDER MAIN TEMPLATE --
                return `
            <div class="bg-cover bg-center p-6 text-secondary">
                <div class="flex items-start justify-between">
                    <div class="space-y-1">
                        <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-lg backdrop-blur-sm">
                            <img src="${profile.company.logo_url}" alt="Logo" />
                        </div>
                        <p class="text-lg font-bold">${profile.company.name}</p>
                        <div class="space-y-0.5 text-xs text-slate-200">
                            <p>${profile.contact.street} · ${profile.contact.city}</p>
                            <a href="tel:${profile.contact.phone_link}" class="text-slate-300 hover:text-slate-300">Tel: ${profile.contact.phone_display}</a>
                        </div>
                    </div>
                    <div class="text-right">
                        <h1 class="mb-3 text-4xl font-bold tracking-tight uppercase">${config.title}</h1>
                        <div class="inline-block rounded-lg border border-secondary/20 bg-secondary/10 p-4 px-3 py-2 text-secondary backdrop-blur-sm">
                            <p class="text-xs font-medium text-slate-300">${config.idLabel}</p>
                            <p class="text-xl font-bold">#${config.idPrefix}${data.year}-${data.nr}</p>
                        </div>
                    </div>
                </div>
            </div>
            <hr />

            <div class="h-full px-10 py-6">
                <div class="mb-6 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-lg border border-secondary/20 bg-secondary/10 p-4 text-secondary backdrop-blur-sm">
                        <p class="mb-2 text-xs font-semibold tracking-wider uppercase">${isInvoice ? 'Rechnung an' : 'Angebot für'}</p>
                        <div class="space-y-0.5 text-sm">
                            <p class="font-semibold">${data.customer.name}</p>
                            <p class="">${data.customer.street}</p>
                            <p class="">${data.customer.city}</p>
                        </div>
                    </div>
                    <div class="rounded-lg border border-secondary/20 bg-secondary/10 p-4 text-xs text-secondary backdrop-blur-sm">
                        <div class="mb-1.5 flex justify-between border-b border-slate-200">
                            <span class="font-medium">${config.dateLabel}:</span>
                            <span class="font-semibold">${data.date}</span>
                        </div>
                        <div class="mb-1.5 flex justify-between border-b border-slate-200">
                            <span class="font-medium">${config.validLabel}:</span>
                            <span class="font-semibold text-red-600">${data.valid_until}</span>
                        </div>
                        ${statusBadgeHtml}
                        <div class="flex justify-between">
                            <span class="font-medium">Steuernummer:</span>
                            <span class="font-mono text-xs">${profile.legal.local_tax_number}</span>
                        </div>
                    </div>
                </div>

                <div class="mb-5 rounded-lg border border-secondary/20 bg-secondary/10 p-4 text-secondary backdrop-blur-sm">
                     <p class="font-semibold">Vielen Dank für Ihr Vertrauen und Ihre Zusammenarbeit!</p>
                </div>

                <div class="mb-6 overflow-hidden rounded-lg border border-secondary/20 bg-secondary/10 p-4 text-secondary backdrop-blur-sm">
                    <table class="w-full text-sm">
                        <thead>
                            <tr>
                                <th class="px-3 py-2.5 text-left font-semibold">Menge</th>
                                <th class="px-3 py-2.5 text-left font-semibold">Beschreibung</th>
                                <th class="px-3 py-2.5 text-right font-semibold">Einzelpreis</th>
                                <th class="px-3 py-2.5 text-right font-semibold">Gesamt</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rowsHtml}
                        </tbody>
                    </table>
                    
                    <div class="mt-3 flex w-full justify-end">
                        <div class="w-full max-w-xs space-y-1.5 text-sm">
                            ${totalsHtml}
                        </div>
                    </div>
                </div>

                ${agbNoteHtml} 

                ${bottomActionHtml}

                <div class="rounded-lg border border-secondary/20 bg-secondary/10 p-4 text-secondary backdrop-blur-sm">
                    <div class="grid gap-6 sm:grid-cols-3">
                        <div>
                            <p class="mb-2 text-xs font-semibold tracking-wider uppercase">Bankverbindung</p>
                            <div class="space-y-0.5 text-xs">
                                <p><span class="font-medium">Kontoinhaber:</span> ${profile.bank.holder}</p>
                                <p><span class="font-medium">BIC:</span> ${profile.bank.bic}</p>
                                <p><span class="font-medium">Bank:</span> ${profile.bank.name}</p>
                                <p><span class="text-base font-medium">IBAN:</span> ${profile.bank.iban}</p>
                            </div>
                        </div>
                        <div>
                            <p class="mb-2 text-xs font-semibold tracking-wider uppercase">Steuerliche Informationen</p>
                            <div class="space-y-0.5 text-xs">
                                <p><span class="font-medium">${profile.legal.tax_id_label} &nbsp;</span> ${profile.legal.tax_id}</p>
                                <p class="mt-1.5 text-xs">${profile.legal.tax_note}</p>
                            </div>
                        </div>
                        <div>
                            <p class="mb-2 text-xs font-semibold tracking-wider uppercase">Kontakt</p>
                            <p class="text-xs">Bei Fragen kontaktieren Sie mich unter:</p>
                            <a href="mailto:${profile.contact.email}" class="mt-1 inline-block text-xs font-medium text-blue-600 hover:text-blue-700">${profile.contact.email}</a>
                        </div>
                    </div>
                </div>
            </div>
        `;
            }

            // INIT
            async function init() {
                try {
                    const [docRes, profileRes] = await Promise.all([fetch('./data.json'), fetch('./private.json')]);

                    if (!docRes.ok || !profileRes.ok) throw new Error('Fehler beim Laden der Dateien.');

                    const docData = await docRes.json();
                    const profileData = await profileRes.json();

                    document.title = `${docData.type === 'invoice' ? 'Rechnung' : 'Angebot'} #${docData.nr}`;
                    document.getElementById('document-content').innerHTML = renderDocument(docData, profileData);
                } catch (error) {
                    console.error(error);
                    document.getElementById('document-content').innerHTML = `
                <div class="p-10 text-center text-red-600 bg-white">
                    <strong>Fehler:</strong> ${error.message}<br>
                    Nutze "Live Server" (VS Code) oder lade die Dateien auf einen Webserver.
                </div>
            `;
                }
            }

            init();
        </script>
    </body>
</html>
