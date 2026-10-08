(() => {
  'use strict';
  const translations = {
    en: {
      '営業ダッシュボード':'Sales dashboard','営業履歴':'Sales activities','会社・担当者':'Companies & contacts',
      '会社':'Companies','製品 / SDS':'Products / SDS','製品/SDS':'Products / SDS',
      '営業 横串ビュー':'Cross-company sales overview','横串ビュー':'Overview','試験・現場テスト':'Tests & field trials','試験':'Tests',
      'ユーザー管理':'User management','初期設定':'Initial setup','パスワード変更':'Change password',
      '次のアクション':'Next actions','予定試験':'Upcoming tests','最近の営業履歴':'Recent sales activities',
      '新規営業記録':'New activity','履歴':'History','会社一覧':'Company list','会社追加':'Add company',
      '担当者名刺カード':'Contact business cards','商品ごと':'By product','会社ごと':'By company',
      '商品':'Product','会社':'Company','段階':'Stage','現在地':'Current status','次アクション':'Next action',
      '製品 / SDS':'Products / SDS','SDSファイル':'SDS files','ユーザー追加':'Add user',
      '表示名':'Display name','ユーザー名':'Username','新しいパスワード':'New password','初期パスワード':'Initial password',
      '管理者を登録':'Register administrator','ログイン':'Log in','変更して開始':'Save and continue',
      'ユーザーを追加しました。初回パスワードは0921、ログイン後に変更必須です。':'User added; password change required at first login.',
      '日付':'Date','担当者':'Contact','種別':'Type','件名':'Subject','日本語':'Japanese','期限':'Due date',
      '保存':'Save','完了':'Done','国':'Country','メモ':'Notes','追加':'Add','会社名':'Company name',
      '試験材':'Test material','状況':'Status','権限':'Role',
      '同じ商品が、どの会社で・どの段階まで進んでいるかを横断して確認します。':'Compare each product across companies and progress stages.',
      '各社で動いている商品・テーマ・次の一手をまとめて確認します。':'Review each company’s products, themes and next actions.',
      'SDSは既存の':'SDS files use the existing',
      '初回ログインです。新しいパスワードを登録してください。':'First login: please set a new password.',
      'ユーザー名またはパスワードが違います。':'Invalid username or password.',
      'ユーザー名が使用済みです。':'Username already exists.'
    },
    pl: {
      '営業ダッシュボード':'Panel sprzedaży','営業履歴':'Historia działań','会社・担当者':'Firmy i kontakty',
      '会社':'Firmy','製品 / SDS':'Produkty / SDS','製品/SDS':'Produkty / SDS',
      '営業 横串ビュー':'Przegląd sprzedaży','横串ビュー':'Przegląd','試験・現場テスト':'Testy terenowe','試験':'Testy',
      'ユーザー管理':'Zarządzanie użytkownikami','初期設定':'Konfiguracja początkowa','パスワード変更':'Zmiana hasła',
      '次のアクション':'Następne działania','予定試験':'Planowane testy','最近の営業履歴':'Ostatnie działania',
      '新規営業記録':'Nowe działanie','履歴':'Historia','会社一覧':'Lista firm','会社追加':'Dodaj firmę',
      '担当者名刺カード':'Wizytówki kontaktów','商品ごと':'Według produktu','会社ごと':'Według firmy',
      '商品':'Produkt','会社':'Firma','段階':'Etap','現在地':'Aktualny stan','次アクション':'Następne działanie',
      'SDSファイル':'Pliki SDS','ユーザー追加':'Dodaj użytkownika',
      '表示名':'Nazwa wyświetlana','ユーザー名':'Nazwa użytkownika','新しいパスワード':'Nowe hasło','初期パスワード':'Hasło początkowe',
      '管理者を登録':'Utwórz administratora','ログイン':'Zaloguj się','変更して開始':'Zapisz i kontynuuj',
      '日付':'Data','担当者':'Kontakt','種別':'Typ','件名':'Temat','日本語':'Japoński','期限':'Termin',
      '保存':'Zapisz','完了':'Zakończ','国':'Kraj','メモ':'Notatki','追加':'Dodaj','会社名':'Nazwa firmy',
      '状況':'Status','権限':'Rola',
      '同じ商品が、どの会社で・どの段階まで進んでいるかを横断して確認します。':'Porównaj postępy produktów w poszczególnych firmach.',
      '各社で動いている商品・テーマ・次の一手をまとめて確認します。':'Przejrzyj produkty, tematy i następne kroki dla każdej firmy.',
      '初回ログインです。新しいパスワードを登録してください。':'Pierwsze logowanie: ustaw nowe hasło.',
      'ユーザー名またはパスワードが違います。':'Nieprawidłowa nazwa użytkownika lub hasło.'
    }
  };
  const original = new WeakMap();
  const saved = localStorage.getItem('daisho-crm-language');
  let language = ['ja','en','pl'].includes(saved) ? saved : 'ja';
  function applyLanguage() {
    const dict = translations[language] || {};
    document.documentElement.lang = language;
    const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
    let node;
    while ((node = walker.nextNode())) {
      if (node.parentElement && ['SCRIPT','STYLE','TEXTAREA','OPTION'].includes(node.parentElement.tagName)) continue;
      if (!original.has(node)) original.set(node,node.textContent);
      const source = original.get(node);
      const key = source.trim();
      if (!key) continue;
      const translated = dict[key];
      node.textContent = translated === undefined ? source : source.replace(key,translated);
    }
    document.querySelectorAll('[data-crm-summary]').forEach(el => {
      const available = el.dataset['summary' + language.charAt(0).toUpperCase() + language.slice(1)] || '';
      el.textContent = available || el.dataset.summaryJa || '';
    });
    document.title = document.title.replace(/^(営業ダッシュボード|営業履歴|会社・担当者|営業 横串ビュー|試験・現場テスト|ユーザー管理|初期設定|パスワード変更)/, k => dict[k] || k);
  }
  function init() {
    const top = document.querySelector('header.top') || document.querySelector('main.wrap');
    if (!top) return;
    const label = document.createElement('label');
    label.className = 'crm-lang-control';
    label.textContent = 'Language ';
    const select = document.createElement('select');
    select.setAttribute('aria-label','Display language');
    for (const [code,name] of [['ja','日本語'],['en','English'],['pl','Polski']]) {
      const option=document.createElement('option'); option.value=code;option.textContent=name;select.append(option);
    }
    select.value=language;
    select.addEventListener('change', () => {
      language=select.value;
      localStorage.setItem('daisho-crm-language', language);
      applyLanguage();
    });
    label.append(select);
    top.prepend(label);
    applyLanguage();
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded',init); else init();
})();