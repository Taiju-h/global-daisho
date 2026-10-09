<?php
declare(strict_types=1);
if(!defined('APP_NAME')) { http_response_code(404); exit; }
// Read-only snapshot: Google Sheets 実験管理 / 実験, checked 2026-10-10 JST.
return json_decode(<<<'EXPERIMENTS_JSON'
[
  {
    "number": 101,
    "source_key": "1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI:531133730:101",
    "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=531133730&range=A2:L2",
    "source_checked_on": "2026-10-10",
    "title": "ベントナイトダマ邂逅実験",
    "client_original": "清水建設フィリピン",
    "company": "清水建設株式会社",
    "site": "フィリピン",
    "product_code": "S-CHEM",
    "dates": {
      "due": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "requested": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "planned": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "performed": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "followup": {
        "display": "",
        "iso": null,
        "serial": null
      }
    },
    "purpose": "ベントナイトに水だけでダマにならないようにして邂逅できるか、塩化カリウム、エスケム、ヘキメタを入れます",
    "notes": "",
    "result": "",
    "media_group": "",
    "media": [],
    "status": "unconfirmed",
    "original_values": [
      "101",
      "ベントナイトダマ邂逅実験",
      "清水建設フィリピン",
      "",
      "",
      "",
      "",
      "",
      "ベントナイトに水だけでダマにならないようにして邂逅できるか、塩化カリウム、エスケム、ヘキメタを入れます\n",
      "",
      "",
      ""
    ],
    "translations": {
      "en": {
        "title": "Bentonite lump test",
        "purpose": "Check whether bentonite can disperse in water without forming lumps; add potassium chloride, S-Chem and ヘキメタ (source wording)."
      },
      "pl": {
        "title": "Badanie grudek bentonitu",
        "purpose": "Sprawdzić, czy bentonit może rozproszyć się w wodzie bez grudek; dodać chlorek potasu, S-Chem i ヘキメタ (nazwa w źródle)."
      }
    }
  },
  {
    "number": 102,
    "source_key": "1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI:531133730:102",
    "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=531133730&range=A3:L3",
    "source_checked_on": "2026-10-10",
    "title": "横浜シルトのエスケム実験",
    "client_original": "ディケイコム",
    "company": "ディケイコム",
    "site": "",
    "product_code": "S-CHEM",
    "dates": {
      "due": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "requested": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "planned": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "performed": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "followup": {
        "display": "",
        "iso": null,
        "serial": null
      }
    },
    "purpose": "エスケムをいれてフロー値が上がるか実験",
    "notes": "",
    "result": "",
    "media_group": "",
    "media": [],
    "status": "unconfirmed",
    "original_values": [
      "102",
      "横浜シルトのエスケム実験",
      "ディケイコム",
      "",
      "",
      "",
      "",
      "",
      "エスケムをいれてフロー値が上がるか実験",
      "",
      "",
      ""
    ],
    "translations": {
      "en": {
        "title": "S-Chem test on Yokohama silt",
        "purpose": "Test whether adding S-Chem increases the flow value."
      },
      "pl": {
        "title": "Badanie S-Chem z pyłem z Jokohamy",
        "purpose": "Sprawdzić, czy dodanie S-Chem zwiększa rozpływ."
      }
    }
  },
  {
    "number": 103,
    "source_key": "1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI:531133730:103",
    "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=531133730&range=A4:L4",
    "source_checked_on": "2026-10-10",
    "title": "貫入試験、削孔試験",
    "client_original": "東急建設",
    "company": "東急建設株式会社",
    "site": "",
    "product_code": null,
    "dates": {
      "due": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "requested": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "planned": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "performed": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "followup": {
        "display": "",
        "iso": null,
        "serial": null
      }
    },
    "purpose": "",
    "notes": "8/31 ヘキメタ3%混ぜた水とただの水をそれぞれの種類に入れる。 セメントパターン1 +ヘキメタ3%混ぜた セメントパターン1 *水 セメントパターン2 +ヘキメタ3%混ぜた セメントパターン2 *水",
    "result": "",
    "media_group": "",
    "media": [],
    "status": "unconfirmed",
    "original_values": [
      "103",
      "貫入試験、削孔試験",
      "東急建設",
      "",
      "",
      "",
      "",
      "",
      "",
      "8/31 ヘキメタ3%混ぜた水とただの水をそれぞれの種類に入れる。 セメントパターン1 +ヘキメタ3%混ぜた セメントパターン1 *水 セメントパターン2 +ヘキメタ3%混ぜた セメントパターン2 *水 ",
      "",
      ""
    ],
    "translations": {
      "en": {
        "title": "Penetration and drilling tests",
        "notes": "8/31: use water containing 3% ヘキメタ and plain water with each type. Cement pattern 1 + water containing 3% ヘキメタ; cement pattern 1 + water; cement pattern 2 + water containing 3% ヘキメタ; cement pattern 2 + water. The year of 8/31 is not stated in this note."
      },
      "pl": {
        "title": "Badania penetracji i wiercenia",
        "notes": "8/31: dla każdego rodzaju użyć wody z 3% ヘキメタ i czystej wody. Wariant cementu 1 + woda z 3% ヘキメタ; wariant 1 + woda; wariant 2 + woda z 3% ヘキメタ; wariant 2 + woda. W notatce nie podano roku dla daty 8/31."
      }
    }
  },
  {
    "number": 104,
    "source_key": "1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI:531133730:104",
    "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=531133730&range=A5:L5",
    "source_checked_on": "2026-10-10",
    "title": "テーブルフロー",
    "client_original": "",
    "company": null,
    "site": "",
    "product_code": "S-CHEM",
    "dates": {
      "due": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "requested": {
        "display": "9/8",
        "iso": "2026-09-08",
        "serial": 46273
      },
      "planned": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "performed": {
        "display": "9/8",
        "iso": "2026-09-08",
        "serial": 46273
      },
      "followup": {
        "display": "",
        "iso": null,
        "serial": null
      }
    },
    "purpose": "",
    "notes": "wc100, schem 粉3:9,ソーダ灰4:9, ブランクの3つ。土：1 m³にたいして\nセメント：150～170 kg",
    "result": "",
    "media_group": "",
    "media": [],
    "status": "recorded",
    "original_values": [
      "104",
      "テーブルフロー",
      "",
      "",
      "9/8",
      "",
      "9/8",
      "",
      "",
      "wc100, schem 粉3:9,ソーダ灰4:9, ブランクの3つ。土：1 m³にたいして\nセメント：150～170 kg\n\n",
      "",
      ""
    ],
    "translations": {
      "en": {
        "title": "Table flow test",
        "notes": "wc100; three conditions: schem powder 3:9, soda ash 4:9, and a blank. Per 1 m³ of soil: cement 150–170 kg. Ratios and notation are retained from the source."
      },
      "pl": {
        "title": "Badanie rozpływu na stoliku",
        "notes": "wc100; trzy warunki: proszek schem 3:9, soda kalcynowana 4:9 i próba bez dodatku. Na 1 m³ gruntu: cement 150–170 kg. Zachowano proporcje i zapis źródłowy."
      }
    }
  },
  {
    "number": 105,
    "source_key": "1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI:531133730:105",
    "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=531133730&range=A6:L6",
    "source_checked_on": "2026-10-10",
    "title": "ベントナイト・混和剤配合試験",
    "client_original": "清水建設フィリピン",
    "company": "清水建設株式会社",
    "site": "フィリピン",
    "product_code": null,
    "dates": {
      "due": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "requested": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "planned": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "performed": {
        "display": "2026/5/28",
        "iso": "2026-05-28",
        "serial": 46170
      },
      "followup": {
        "display": "",
        "iso": null,
        "serial": null
      }
    },
    "purpose": "ベントナイト濃度とPAM等の混和剤条件による分散・練混ぜ性を確認",
    "notes": "記録期間 2026/5/28〜6/18。配合表、試料写真、混合動画を収録。",
    "result": "ベントナイト10・20・30%配合、PAM・アルギン酸ナトリウム等の添加量比較を記録。数値結果は資料原本参照。",
    "media_group": "01_ベントナイト・混和剤配合試験",
    "media": [
      {
        "date": "2026/5/28",
        "type": "写真",
        "filename": "2026-05-28_1504_ベントナイト配合表.jpeg",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A2:E2"
      },
      {
        "date": "2026/6/2",
        "type": "写真",
        "filename": "2026-06-02_1259_混和剤添加量表.jpeg",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A4:E4"
      },
      {
        "date": "2026/6/2",
        "type": "動画",
        "filename": "2026-06-02_1737_カップ材料混合試験_01.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A5:E5"
      },
      {
        "date": "2026/6/2",
        "type": "動画",
        "filename": "2026-06-02_1737_カップ材料混合試験_02.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A6:E6"
      },
      {
        "date": "2026/6/2",
        "type": "動画",
        "filename": "2026-06-02_1737_カップ材料混合試験_03.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A7:E7"
      },
      {
        "date": "2026/6/2",
        "type": "動画",
        "filename": "2026-06-02_1738_カップ材料混合試験_04.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A8:E8"
      },
      {
        "date": "2026/6/2",
        "type": "動画",
        "filename": "2026-06-02_1738_カップ材料混合試験_05.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A9:E9"
      },
      {
        "date": "2026/6/2",
        "type": "動画",
        "filename": "2026-06-02_1738_カップ材料混合試験_06.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A10:E10"
      },
      {
        "date": "2026/6/2",
        "type": "動画",
        "filename": "2026-06-02_1738_カップ材料混合試験_07.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A11:E11"
      },
      {
        "date": "2026/6/2",
        "type": "動画",
        "filename": "2026-06-02_1738_カップ材料混合試験_08.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A12:E12"
      },
      {
        "date": "2026/6/3",
        "type": "動画",
        "filename": "2026-06-03_1719_カップ材料混合試験_09.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A14:E14"
      },
      {
        "date": "2026/6/3",
        "type": "動画",
        "filename": "2026-06-03_1719_カップ材料混合試験_10.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A15:E15"
      },
      {
        "date": "2026/6/3",
        "type": "動画",
        "filename": "2026-06-03_1719_カップ材料混合試験_11.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A16:E16"
      },
      {
        "date": "2026/6/8",
        "type": "動画",
        "filename": "2026-06-08_1601_ベントナイト配合比較.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A18:E18"
      },
      {
        "date": "2026/6/10",
        "type": "動画",
        "filename": "2026-06-10_1517_ベントナイト配合比較_01.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A23:E23"
      },
      {
        "date": "2026/6/18",
        "type": "動画",
        "filename": "2026-06-18_1011_ベントナイト配合比較_02.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A27:E27"
      }
    ],
    "status": "recorded",
    "original_values": [
      "105",
      "ベントナイト・混和剤配合試験",
      "清水建設フィリピン",
      "",
      "",
      "",
      "2026/5/28",
      "",
      "ベントナイト濃度とPAM等の混和剤条件による分散・練混ぜ性を確認",
      "記録期間 2026/5/28〜6/18。配合表、試料写真、混合動画を収録。",
      "ベントナイト10・20・30%配合、PAM・アルギン酸ナトリウム等の添加量比較を記録。数値結果は資料原本参照。",
      "01_ベントナイト・混和剤配合試験"
    ],
    "translations": {
      "en": {
        "title": "Bentonite and admixture formulation tests",
        "purpose": "Check dispersion and mixing behavior for different bentonite concentrations and admixture conditions, including PAM.",
        "notes": "Records cover 28 May–18 June 2026. Includes formulation tables, sample photographs and mixing videos.",
        "result": "Recorded bentonite formulations of 10%, 20% and 30%, and comparisons of PAM, sodium alginate and other additive dosages. Refer to the original materials for numerical results."
      },
      "pl": {
        "title": "Badania receptur bentonitu i domieszek",
        "purpose": "Sprawdzić dyspersję i mieszalność przy różnych stężeniach bentonitu i warunkach stosowania domieszek, w tym PAM.",
        "notes": "Dokumentacja z 28 maja–18 czerwca 2026. Obejmuje tabele receptur, zdjęcia próbek i filmy z mieszania.",
        "result": "Zapisano receptury z bentonitem 10%, 20% i 30% oraz porównania dozowania PAM, alginianu sodu i innych dodatków. Wyniki liczbowe znajdują się w materiałach źródłowych."
      }
    }
  },
  {
    "number": 106,
    "source_key": "1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI:531133730:106",
    "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=531133730&range=A7:L7",
    "source_checked_on": "2026-10-10",
    "title": "懸濁液・沈降試験",
    "client_original": "防水効果を測る実験",
    "company": null,
    "site": "",
    "product_code": null,
    "dates": {
      "due": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "requested": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "planned": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "performed": {
        "display": "2026/6/8",
        "iso": "2026-06-08",
        "serial": 46181
      },
      "followup": {
        "display": "",
        "iso": null,
        "serial": null
      }
    },
    "purpose": "懸濁液の沈降、残渣、容器内状態を経時確認",
    "notes": "記録期間 2026/6/8〜7/1。",
    "result": "計量容器・カップ内の懸濁状態と沈降残渣を動画で比較。定量値は記録なし。",
    "media_group": "02_懸濁液・沈降試験",
    "media": [
      {
        "date": "2026/6/8",
        "type": "動画",
        "filename": "2026-06-08_1547_容器内材料状態確認.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A17:E17"
      },
      {
        "date": "2026/6/8",
        "type": "動画",
        "filename": "2026-06-08_1831_懸濁液沈降状態確認_01.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A19:E19"
      },
      {
        "date": "2026/6/8",
        "type": "動画",
        "filename": "2026-06-08_1831_懸濁液沈降状態確認_02.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A20:E20"
      },
      {
        "date": "2026/6/8",
        "type": "動画",
        "filename": "2026-06-08_1831_懸濁液沈降状態確認_03.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A21:E21"
      },
      {
        "date": "2026/6/30",
        "type": "動画",
        "filename": "2026-06-30_1910_懸濁液状態確認.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A37:E37"
      },
      {
        "date": "2026/6/30",
        "type": "動画",
        "filename": "2026-06-30_1910_沈降残渣確認.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A38:E38"
      },
      {
        "date": "2026/7/1",
        "type": "動画",
        "filename": "2026-07-01_1607_材料混合試験_計量カップ.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A39:E39"
      }
    ],
    "status": "recorded",
    "original_values": [
      "106",
      "懸濁液・沈降試験",
      "防水効果を測る実験",
      "",
      "",
      "",
      "2026/6/8",
      "",
      "懸濁液の沈降、残渣、容器内状態を経時確認",
      "記録期間 2026/6/8〜7/1。",
      "計量容器・カップ内の懸濁状態と沈降残渣を動画で比較。定量値は記録なし。",
      "02_懸濁液・沈降試験"
    ],
    "translations": {
      "en": {
        "title": "Suspension and sedimentation tests",
        "purpose": "Observe suspension sedimentation, residue and conditions inside containers over time.",
        "notes": "Records cover 8 June–1 July 2026.",
        "result": "Videos compare suspension conditions and sediment residues in measuring containers and cups. No quantitative values are recorded."
      },
      "pl": {
        "title": "Badania zawiesin i sedymentacji",
        "purpose": "Obserwować w czasie sedymentację zawiesin, osad i stan materiału w pojemnikach.",
        "notes": "Dokumentacja z 8 czerwca–1 lipca 2026.",
        "result": "Filmy porównują stan zawiesin i osady w pojemnikach pomiarowych i kubkach. Brak zapisanych wartości ilościowych."
      }
    }
  },
  {
    "number": 107,
    "source_key": "1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI:531133730:107",
    "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=531133730&range=A8:L8",
    "source_checked_on": "2026-10-10",
    "title": "粉体・粒状材料評価",
    "client_original": "ポーランド、DKコム用に",
    "company": "ディケイコム",
    "site": "",
    "product_code": null,
    "dates": {
      "due": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "requested": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "planned": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "performed": {
        "display": "2026/5/29",
        "iso": "2026-05-29",
        "serial": 46171
      },
      "followup": {
        "display": "",
        "iso": null,
        "serial": null
      }
    },
    "purpose": "PAASの効果測定。粉体・粒状材料の外観、粒径感、切削・混合時の状態を確認",
    "notes": "記録期間 2026/5/29〜9/8。LDC-40P、中国製材料、白色粒状材料等。",
    "result": "外観、粒状状態、切削・混合状態を写真・動画で記録。材料名が判読できないものは断定せず。",
    "media_group": "03_粉体・粒状材料評価",
    "media": [
      {
        "date": "2026/5/29",
        "type": "写真",
        "filename": "2026-05-29_1015_材料サンプル6種.jpeg",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A3:E3"
      },
      {
        "date": "2026/6/10",
        "type": "動画",
        "filename": "2026-06-10_1517_材料サンプル切削確認.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A24:E24"
      },
      {
        "date": "2026/6/10",
        "type": "写真",
        "filename": "2026-06-10_1728_LDC-40Pサンプル袋.jpeg",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A25:E25"
      },
      {
        "date": "2026/6/12",
        "type": "写真",
        "filename": "2026-06-12_1414_中国製材料サンプル袋.jpeg",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A26:E26"
      },
      {
        "date": "2026/6/18",
        "type": "動画",
        "filename": "2026-06-18_1011_材料サンプル切削確認_02.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A28:E28"
      },
      {
        "date": "2026/6/18",
        "type": "動画",
        "filename": "2026-06-18_1736_粉体材料_混合状態確認.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A29:E29"
      },
      {
        "date": "2026/6/26",
        "type": "写真",
        "filename": "2026-06-26_1133_LDC-40Pサンプル袋_02.jpeg",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A30:E30"
      },
      {
        "date": "2026/6/26",
        "type": "写真",
        "filename": "2026-06-26_1203_中国製材料サンプル袋_02.jpeg",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A31:E31"
      },
      {
        "date": "2026/6/30",
        "type": "写真",
        "filename": "2026-06-30_1854_白色粒状材料_拡大_01.jpeg",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A33:E33"
      },
      {
        "date": "2026/6/30",
        "type": "写真",
        "filename": "2026-06-30_1854_白色粒状材料_拡大_02.jpeg",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A34:E34"
      },
      {
        "date": "2026/6/30",
        "type": "写真",
        "filename": "2026-06-30_1854_白色粒状材料_拡大_03.jpeg",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A35:E35"
      },
      {
        "date": "2026/6/30",
        "type": "写真",
        "filename": "2026-06-30_1854_白色粒状材料.jpeg",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A36:E36"
      },
      {
        "date": "2026/7/1",
        "type": "動画",
        "filename": "2026-07-01_1808_粉体サンプル比較_01.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A40:E40"
      },
      {
        "date": "2026/7/1",
        "type": "動画",
        "filename": "2026-07-01_1808_粉体サンプル比較_02.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A41:E41"
      },
      {
        "date": "2026/9/8",
        "type": "写真",
        "filename": "2026-09-08_1442_LDC-40Pサンプル袋_03.jpeg",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A64:E64"
      },
      {
        "date": "2026/9/8",
        "type": "動画",
        "filename": "2026-09-08_1551_乾燥材料の塊状状態_01.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A65:E65"
      },
      {
        "date": "2026/9/8",
        "type": "動画",
        "filename": "2026-09-08_1607_乾燥材料の塊状状態_02.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A66:E66"
      }
    ],
    "status": "recorded",
    "original_values": [
      "107",
      "粉体・粒状材料評価",
      "ポーランド、DKコム用に",
      "",
      "",
      "",
      "2026/5/29",
      "",
      "PAASの効果測定。粉体・粒状材料の外観、粒径感、切削・混合時の状態を確認",
      "記録期間 2026/5/29〜9/8。LDC-40P、中国製材料、白色粒状材料等。",
      "外観、粒状状態、切削・混合状態を写真・動画で記録。材料名が判読できないものは断定せず。",
      "03_粉体・粒状材料評価"
    ],
    "translations": {
      "en": {
        "title": "Powder and granular material evaluation",
        "purpose": "Evaluate PAAS effects. Check appearance, apparent particle size and material behavior during cutting and mixing.",
        "notes": "Records cover 29 May–8 September 2026. Includes LDC-40P, Chinese materials and white granular materials.",
        "result": "Photographs and videos record appearance, granularity, cutting and mixing behavior. Material names that cannot be read are not identified conclusively."
      },
      "pl": {
        "title": "Ocena materiałów proszkowych i ziarnistych",
        "purpose": "Ocenić działanie PAAS. Sprawdzić wygląd, orientacyjną wielkość ziaren i zachowanie materiału podczas cięcia i mieszania.",
        "notes": "Dokumentacja z 29 maja–8 września 2026. Obejmuje LDC-40P, materiały chińskie i białe materiały ziarniste.",
        "result": "Zdjęcia i filmy dokumentują wygląd, ziarnistość oraz zachowanie podczas cięcia i mieszania. Nie ustalono nazw materiałów, których oznaczeń nie można odczytać."
      }
    }
  },
  {
    "number": 108,
    "source_key": "1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI:531133730:108",
    "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=531133730&range=A9:L9",
    "source_checked_on": "2026-10-10",
    "title": "荒木田土・締固め試験",
    "client_original": "ポーランド、DKコム用に",
    "company": "ディケイコム",
    "site": "",
    "product_code": "S-CHEM",
    "dates": {
      "due": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "requested": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "planned": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "performed": {
        "display": "2026/7/6",
        "iso": "2026-07-06",
        "serial": 46209
      },
      "followup": {
        "display": "",
        "iso": null,
        "serial": null
      }
    },
    "purpose": "ソイルミキシングにおけるS-Chem基本配合を検討。荒木田土で粘土質の原位置土を模擬し、PAASとの軟化・流動化効果、成形性・締固め性を比較。",
    "notes": "記録期間 2026/7/6〜7/14。\n基本構成：荒木田土（模擬土）＋水＋セメント＋S-Chem。\n比較条件：PAAS 3・3.5・4およびS-Chem。\nMessenger記録：PAAS 3とS-Chemは同等、PAAS 4は3より軟らかい。\n数値の単位・各材料量は未確認。",
    "result": "ソイルミキシング配合検討用。普通土ではなくクレイ状材料が必要なため、油粘土・カオリンを除外して荒木田土を採用。締固め工程と試料状態を写真・動画で記録。定量フロー値と確定配合は記録なし。",
    "media_group": "04_荒木田土・締固め試験",
    "media": [
      {
        "date": "2026/7/6",
        "type": "動画",
        "filename": "2026-07-06_1320_粘土状材料の状態確認.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A43:E43"
      },
      {
        "date": "2026/7/13",
        "type": "写真",
        "filename": "2026-07-13_1211_荒木田土_製品袋.jpeg",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A44:E44"
      },
      {
        "date": "2026/7/14",
        "type": "動画",
        "filename": "2026-07-14_1622_締固め試験_01.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A45:E45"
      },
      {
        "date": "2026/7/14",
        "type": "動画",
        "filename": "2026-07-14_1623_締固め試験_02.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A46:E46"
      },
      {
        "date": "2026/7/14",
        "type": "動画",
        "filename": "2026-07-14_1623_締固め試験_03.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A47:E47"
      }
    ],
    "status": "recorded",
    "original_values": [
      "108",
      "荒木田土・締固め試験",
      "ポーランド、DKコム用に",
      "",
      "",
      "",
      "2026/7/6",
      "",
      "ソイルミキシングにおけるS-Chem基本配合を検討。荒木田土で粘土質の原位置土を模擬し、PAASとの軟化・流動化効果、成形性・締固め性を比較。",
      "記録期間 2026/7/6〜7/14。\n基本構成：荒木田土（模擬土）＋水＋セメント＋S-Chem。\n比較条件：PAAS 3・3.5・4およびS-Chem。\nMessenger記録：PAAS 3とS-Chemは同等、PAAS 4は3より軟らかい。\n数値の単位・各材料量は未確認。",
      "ソイルミキシング配合検討用。普通土ではなくクレイ状材料が必要なため、油粘土・カオリンを除外して荒木田土を採用。締固め工程と試料状態を写真・動画で記録。定量フロー値と確定配合は記録なし。",
      "04_荒木田土・締固め試験"
    ],
    "translations": {
      "en": {
        "title": "Arakida soil and compaction tests",
        "purpose": "Investigate a basic S-Chem formulation for soil mixing. Use Arakida soil to simulate clayey in-situ soil and compare softening, fluidization, moldability and compactability against PAAS.",
        "notes": "Records cover 6–14 July 2026. Basic components: Arakida soil (simulated soil), water, cement and S-Chem. Comparison conditions: PAAS 3, 3.5 and 4, and S-Chem. Messenger record: PAAS 3 and S-Chem were equivalent; PAAS 4 was softer than PAAS 3. Units and individual material quantities are not confirmed.",
        "result": "For soil-mixing formulation development. A clay-like material was needed rather than ordinary soil, so oil clay and kaolin were excluded and Arakida soil was chosen. Photographs and videos record the compaction process and sample conditions. No quantitative flow values or finalized formulation are recorded."
      },
      "pl": {
        "title": "Badania gruntu Arakida i zagęszczania",
        "purpose": "Opracować podstawową recepturę S-Chem do mieszania gruntu. Użyć gruntu Arakida do symulacji gruntu ilastego in situ i porównać z PAAS zmiękczanie, upłynnianie, formowalność i podatność na zagęszczanie.",
        "notes": "Dokumentacja z 6–14 lipca 2026. Skład podstawowy: grunt Arakida (grunt modelowy), woda, cement i S-Chem. Porównanie: PAAS 3, 3,5 i 4 oraz S-Chem. Zapis z Messengera: PAAS 3 i S-Chem były równoważne; PAAS 4 był bardziej miękki niż PAAS 3. Jednostki i ilości poszczególnych składników nie są potwierdzone.",
        "result": "Badania receptury do mieszania gruntu. Potrzebny był materiał gliniasty zamiast zwykłego gruntu, dlatego wykluczono glinę olejową i kaolin, wybierając grunt Arakida. Zdjęcia i filmy dokumentują zagęszczanie oraz stan próbek. Brak ilościowych wyników rozpływu i ostatecznej receptury."
      }
    }
  },
  {
    "number": 109,
    "source_key": "1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI:531133730:109",
    "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=531133730&range=A10:L10",
    "source_checked_on": "2026-10-10",
    "title": "モルタル配合・練混ぜ試験",
    "client_original": "DKコム",
    "company": "ディケイコム",
    "site": "",
    "product_code": "S-CHEM",
    "dates": {
      "due": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "requested": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "planned": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "performed": {
        "display": "2026/8/5",
        "iso": "2026-08-05",
        "serial": 46239
      },
      "followup": {
        "display": "",
        "iso": null,
        "serial": null
      }
    },
    "purpose": "PAM・S-chem等の配合条件とモルタル練混ぜ状態を確認",
    "notes": "記録期間 2026/8/5〜8/10。",
    "result": "配合メモ・試験表と練混ぜ状態を記録。数値結果は資料原本参照。",
    "media_group": "05_モルタル配合・練混ぜ試験",
    "media": [
      {
        "date": "2026/8/5",
        "type": "写真",
        "filename": "2026-08-05_1245_配合試験結果一覧.jpeg",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A52:E52"
      },
      {
        "date": "2026/8/5",
        "type": "写真",
        "filename": "2026-08-05_1623_PAM配合手書きメモ.jpeg",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A53:E53"
      },
      {
        "date": "2026/8/7",
        "type": "写真",
        "filename": "2026-08-07_1356_モルタル試験配合メモ.jpeg",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A54:E54"
      },
      {
        "date": "2026/8/7",
        "type": "写真",
        "filename": "2026-08-07_1358_配合試験結果表と試料写真.jpeg",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A55:E55"
      },
      {
        "date": "2026/8/10",
        "type": "動画",
        "filename": "2026-08-10_1835_モルタル練混ぜ状態_01.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A56:E56"
      },
      {
        "date": "2026/8/10",
        "type": "動画",
        "filename": "2026-08-10_1836_モルタル練混ぜ状態_02.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A57:E57"
      }
    ],
    "status": "recorded",
    "original_values": [
      "109",
      "モルタル配合・練混ぜ試験",
      "DKコム",
      "",
      "",
      "",
      "2026/8/5",
      "",
      "PAM・S-chem等の配合条件とモルタル練混ぜ状態を確認",
      "記録期間 2026/8/5〜8/10。",
      "配合メモ・試験表と練混ぜ状態を記録。数値結果は資料原本参照。",
      "05_モルタル配合・練混ぜ試験"
    ],
    "translations": {
      "en": {
        "title": "Mortar formulation and mixing tests",
        "purpose": "Check formulation conditions, including PAM and S-Chem, and mortar mixing behavior.",
        "notes": "Records cover 5–10 August 2026.",
        "result": "Formulation notes, test tables and mixing behavior are recorded. Refer to the original materials for numerical results."
      },
      "pl": {
        "title": "Badania receptur i mieszania zapraw",
        "purpose": "Sprawdzić warunki receptur, w tym PAM i S-Chem, oraz zachowanie zaprawy podczas mieszania.",
        "notes": "Dokumentacja z 5–10 sierpnia 2026.",
        "result": "Zapisano notatki receptur, tabele badań i zachowanie podczas mieszania. Wyniki liczbowe znajdują się w materiałach źródłowych."
      }
    }
  },
  {
    "number": 110,
    "source_key": "1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI:531133730:110",
    "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=531133730&range=A11:L11",
    "source_checked_on": "2026-10-10",
    "title": "モルタルフロー試験",
    "client_original": "ジャパンパイル",
    "company": "ジャパンパイル株式会社",
    "site": "",
    "product_code": null,
    "dates": {
      "due": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "requested": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "planned": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "performed": {
        "display": "2026/9/8",
        "iso": "2026-09-08",
        "serial": 46273
      },
      "followup": {
        "display": "",
        "iso": null,
        "serial": null
      }
    },
    "purpose": "配合差によるモルタルのフロー性を比較",
    "notes": "記録期間 2026/9/8〜9/10。",
    "result": "成形・脱型・フロー径測定を実施。配合表、測定写真、動画を収録。",
    "media_group": "06_モルタルフロー試験",
    "media": [
      {
        "date": "2026/9/8",
        "type": "動画",
        "filename": "2026-09-08_1618_モルタルフロー試験_成形_01.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A67:E67"
      },
      {
        "date": "2026/9/8",
        "type": "動画",
        "filename": "2026-09-08_1816_モルタルフロー試験_成形_02.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A68:E68"
      },
      {
        "date": "2026/9/8",
        "type": "動画",
        "filename": "2026-09-08_1816_モルタルフロー試験_成形_03.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A69:E69"
      },
      {
        "date": "2026/9/9",
        "type": "動画",
        "filename": "2026-09-09_1658_モルタルフロー試験_成形_04.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A70:E70"
      },
      {
        "date": "2026/9/9",
        "type": "動画",
        "filename": "2026-09-09_1658_モルタルフロー試験_成形_05.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A71:E71"
      },
      {
        "date": "2026/9/9",
        "type": "写真",
        "filename": "2026-09-09_1658_モルタルフロー試験_結果_01.jpeg",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A72:E72"
      },
      {
        "date": "2026/9/9",
        "type": "写真",
        "filename": "2026-09-09_1658_モルタルフロー試験_結果_02.jpeg",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A73:E73"
      },
      {
        "date": "2026/9/10",
        "type": "写真",
        "filename": "2026-09-10_1108_モルタルフロー試験_配合表.jpeg",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A74:E74"
      },
      {
        "date": "2026/9/10",
        "type": "動画",
        "filename": "2026-09-10_1446_モルタルフロー試験_成形_06.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A75:E75"
      },
      {
        "date": "2026/9/10",
        "type": "動画",
        "filename": "2026-09-10_1446_モルタルフロー試験_成形_07.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A76:E76"
      },
      {
        "date": "2026/9/10",
        "type": "写真",
        "filename": "2026-09-10_1446_モルタルフロー試験_結果_03.jpeg",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A77:E77"
      },
      {
        "date": "2026/9/10",
        "type": "写真",
        "filename": "2026-09-10_1446_モルタルフロー試験_結果_04.jpeg",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A78:E78"
      },
      {
        "date": "2026/9/10",
        "type": "動画",
        "filename": "2026-09-10_1529_モルタルフロー試験_成形_08.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A79:E79"
      },
      {
        "date": "2026/9/10",
        "type": "写真",
        "filename": "2026-09-10_1529_モルタルフロー試験_結果_05.jpeg",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A80:E80"
      },
      {
        "date": "2026/9/10",
        "type": "動画",
        "filename": "2026-09-10_1538_モルタルフロー試験_成形_09.mp4",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A81:E81"
      },
      {
        "date": "2026/9/10",
        "type": "写真",
        "filename": "2026-09-10_1538_モルタルフロー試験_結果_06.jpeg",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A82:E82"
      }
    ],
    "status": "recorded",
    "original_values": [
      "110",
      "モルタルフロー試験",
      "ジャパンパイル",
      "",
      "",
      "",
      "2026/9/8",
      "",
      "配合差によるモルタルのフロー性を比較",
      "記録期間 2026/9/8〜9/10。",
      "成形・脱型・フロー径測定を実施。配合表、測定写真、動画を収録。",
      "06_モルタルフロー試験"
    ],
    "translations": {
      "en": {
        "title": "Mortar flow tests",
        "purpose": "Compare mortar flow for different formulations.",
        "notes": "Records cover 8–10 September 2026.",
        "result": "Molding, demolding and flow-diameter measurements were performed. Formulation tables, measurement photographs and videos are included."
      },
      "pl": {
        "title": "Badania rozpływu zapraw",
        "purpose": "Porównać rozpływ zapraw o różnych recepturach.",
        "notes": "Dokumentacja z 8–10 września 2026.",
        "result": "Wykonano formowanie, rozformowanie i pomiary średnicy rozpływu. Dokumentacja obejmuje tabele receptur, zdjęcia pomiarowe i filmy."
      }
    }
  },
  {
    "number": 111,
    "source_key": "1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI:531133730:111",
    "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=531133730&range=A12:L12",
    "source_checked_on": "2026-10-10",
    "title": "関連試験資料・配合データ",
    "client_original": "",
    "company": null,
    "site": "",
    "product_code": null,
    "dates": {
      "due": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "requested": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "planned": {
        "display": "",
        "iso": null,
        "serial": null
      },
      "performed": {
        "display": "2026/6/30",
        "iso": "2026-06-30",
        "serial": 46203
      },
      "followup": {
        "display": "",
        "iso": null,
        "serial": null
      }
    },
    "purpose": "過去試験の配合表・結果表・器具写真をまとめて保管",
    "notes": "記録期間 2026/6/30〜8/28。",
    "result": "試験結果表1件、試験用カップ写真2件。",
    "media_group": "07_試験資料・配合データ",
    "media": [
      {
        "date": "2026/6/30",
        "type": "写真",
        "filename": "2026-06-30_1615_試験結果表.jpeg",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A32:E32"
      },
      {
        "date": "2026/8/28",
        "type": "写真",
        "filename": "2026-08-28_1252_材料試験用カップ_01.jpeg",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A60:E60"
      },
      {
        "date": "2026/8/28",
        "type": "写真",
        "filename": "2026-08-28_1252_材料試験用カップ_02.jpeg",
        "source_url": "https://docs.google.com/spreadsheets/d/1Nj8Yv5kHDimitp_9fvCEMyqwkYUeT_-sR55dXOPE7qI/edit#gid=1501369372&range=A61:E61"
      }
    ],
    "status": "recorded",
    "original_values": [
      "111",
      "関連試験資料・配合データ",
      "",
      "",
      "",
      "",
      "2026/6/30",
      "",
      "過去試験の配合表・結果表・器具写真をまとめて保管",
      "記録期間 2026/6/30〜8/28。",
      "試験結果表1件、試験用カップ写真2件。",
      "07_試験資料・配合データ"
    ],
    "translations": {
      "en": {
        "title": "Related test documents and formulation data",
        "purpose": "Collect and retain historical formulation tables, results tables and equipment photographs.",
        "notes": "Records cover 30 June–28 August 2026.",
        "result": "One test-results table and two photographs of test cups."
      },
      "pl": {
        "title": "Powiązane dokumenty badań i dane receptur",
        "purpose": "Zebrać i przechowywać wcześniejsze tabele receptur, wyników oraz zdjęcia sprzętu.",
        "notes": "Dokumentacja z 30 czerwca–28 sierpnia 2026.",
        "result": "Jedna tabela wyników i dwa zdjęcia kubków do badań."
      }
    }
  }
]
EXPERIMENTS_JSON, true, 512, JSON_THROW_ON_ERROR);
