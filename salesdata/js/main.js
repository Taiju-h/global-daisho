let allData = {};      
let currentLang = 'ja'; 
let currentMode = 'field'; 

// 初期化処理
document.addEventListener('DOMContentLoaded', () => {
    // URLパラメータから言語判定
    const urlParams = new URLSearchParams(window.location.search);
    const langParam = urlParams.get('lang');
    if (langParam) currentLang = langParam;

    loadData();
});

// データの読み込み
async function loadData() {
    try {
        const response = await fetch('data/data.json');
        allData = await response.json();
        
        if (!allData[currentLang]) currentLang = 'ja';

        // 言語スイッチャーの表示を合わせる
        const selector = document.getElementById('language-selector');
        if (selector) selector.value = currentLang;

        renderPage();
    } catch (error) {
        console.error('Data load failed:', error);
    }
}

// 言語切り替え
function setLang(lang) {
    if (allData[lang]) {
        currentLang = lang;
        renderPage();
        // URL更新
        const url = new URL(window.location);
        url.searchParams.set('lang', lang);
        window.history.pushState({}, '', url);
    }
}

// モード選択（カード vs テーブル）
function selectMode(mode) {
    currentMode = mode;
    
    // 選択カードの見た目（簡易切り替え）
    document.querySelectorAll('.mode-card').forEach(card => {
        // 本来はここでactiveクラスを付け替え
    });

    renderContent();
    
    // スクロール
    const mainContent = document.getElementById('main-content');
    if(mainContent) mainContent.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ページ全体のテキスト更新
function renderPage() {
    const t = allData[currentLang];
    if (!t) return;

    // 固定テキストの流し込み
    document.querySelectorAll('[data-i18n]').forEach(el => {
        const key = el.getAttribute('data-i18n');
        const value = key.split('.').reduce((obj, k) => (obj || {})[k], t);
        if (value) el.textContent = value;
    });

    renderContent();
}

// メインコンテンツの描画分岐
function renderContent() {
    const t = allData[currentLang];
    const container = document.getElementById('main-content');
    if (!t || !container) return;

    container.innerHTML = ''; 

    if (currentMode === 'field') {
        renderFieldMode(t, container);
    } else {
        renderProductMode(t, container);
    }
}

// 【モード1：現場・工法から探す（テーブル表示）】
function renderFieldMode(t, container) {
    const sections = t.sections;
    const products = t.products;
    const sectionKeys = ['ground', 'shield', 'marine', 'repair', 'concrete'];

    sectionKeys.forEach(secKey => {
        const secInfo = sections[secKey];
        if (!secInfo) return;

        const secProducts = products.filter(p => p.category === secKey);
        if (secProducts.length === 0) return;

        const html = `
            <div class="section-container">
                <div class="section-header">
                    <h2>${secInfo.title}</h2>
                </div>
                <div class="section-body">
                    <p style="margin-bottom:20px; color:#ccc;">${secInfo.desc}</p>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width:15%">${t.table_headers.name}</th>
                                    <th style="width:30%">${t.table_headers.desc}</th>
                                    <th style="width:15%">${t.table_headers.usage}</th>
                                    <th style="width:20%">${t.table_headers.wow}</th>
                                    <th style="width:20%">${t.table_headers.pkg}</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${secProducts.map(p => `
                                    <tr>
                                        <td class="col-name">${p.name}</td>
                                        <td>
                                            ${p.desc}
                                            ${p.problem ? `<div style="font-size:0.85em; color:#ff6b6b; margin-top:8px; border-left:2px solid #ff6b6b; padding-left:5px;">⚠ ${p.problem}</div>` : ''}
                                        </td>
                                        <td>${p.usage}</td>
                                        <td class="col-wow">${p.wow}</td>
                                        <td>
                                            <div>${p.pkg}</div>
                                            ${p.dosage ? `<div style="font-size:0.85em; color:#81ecec; margin-top:4px;">💧 ${p.dosage}</div>` : ''}
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    });
}

// 【モード2：商品名から探す（グリッドカード表示）】
function renderProductMode(t, container) {
    const products = t.products;

    let html = `<h2 class="grid-mode-title">${t.mode_product_title}</h2>`;
    html += '<div class="product-grid-wrapper">';

    products.forEach(p => {
        const imgSrc = p.image ? p.image : 'img/product1.png';
        
        // 課題（リスク）表示ブロック
        const problemHtml = p.problem ? 
            `<div class="card-problem">
                <span class="problem-label">⚠ RISK / 課題</span>
                <p>${p.problem}</p>
             </div>` : '';

        // 添加量表示ブロック
        const dosageHtml = p.dosage ? 
            `<div class="badge-row">
                <span class="tag-label">Dosage</span>
                <span class="p-pkg" style="color:#81ecec; font-weight:bold;">💧 ${p.dosage}</span>
             </div>` : '';

        html += `
            <div class="product-card-large">
                <div class="card-image-box">
                    <img src="${imgSrc}" alt="${p.name}">
                </div>
                <div class="card-content">
                    <h3>${p.name}</h3>
                    <p class="card-desc">${p.desc}</p>
                    
                    ${problemHtml}

                    <div class="card-footer">
                        <div class="badge-row">
                            <span class="tag-label">PKG</span>
                            <span class="p-pkg">📦 ${p.pkg}</span>
                        </div>
                        
                        ${dosageHtml}
                        
                        <div class="badge-wow">
                            <span class="wow-icon">★</span> ${p.wow}
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    html += '</div>';
    container.innerHTML = html;
}