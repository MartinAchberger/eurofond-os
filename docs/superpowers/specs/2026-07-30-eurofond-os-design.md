# EUROFOND OS — Design v1 (MVP s AI)

Dátum: 2026-07-30
Stav: schválené používateľom (Martin)

## Čo staviame

Pracovný operačný systém pre projektových manažérov, ktorí pripravujú eurofondové,
dotačné a grantové projekty. Jeden zdroj pravdy pre celé portfólio: projekty,
dokumenty s verziami, otázky/odpovede/rozhodnutia, úlohy s dôkazmi, riziká,
kontrolné brány a AI vrstva, ktorá navrhuje — nikdy nerozhoduje.

**Zásadný princíp:** systém nesmie zameniť predpoklad za potvrdenú skutočnosť.
AI navrhne → človek skontroluje/schváli → systém zaznamená rozhodnutie a dôkaz.

Prvá verzia: webová aplikácia na serveri, jeden PM s prihlásením (dátový model
pripravený na neskorších kolegov a roly).

## Stack

| Vrstva | Technológia |
|---|---|
| Backend | Laravel 12, PHP 8.3+ |
| Databáza | MySQL |
| Administrácia | Orchid (číselníky, používatelia, audit, surové dáta) |
| PM rozhranie | Livewire 3 + Tailwind CSS (podľa mockupu) |
| Background joby | Laravel queues (database driver) |
| AI | Claude API cez oficiálne PHP SDK (`anthropic-ai/sdk`), model `claude-opus-5` |
| Dokumenty | Laravel storage (lokálny disk servera), prístup len cez auth |

PDF sa posielajú Claude priamo (natívna podpora dokumentov v Messages API,
base64 document block). XLSX sa pred odoslaním prevedú na text cez PhpSpreadsheet.

## Dátový model

- **Client** — žiadateľ/prijímateľ (obec, firma). Kontakty.
- **Project** — kód (PRJ-XXX), názov, klient, výzva, rozpočet, požadovaný
  príspevok, fáza (1–12), status, zdravie (dobré/stredné/riziko), najbližší
  deadline, hlavný blokátor, nasledujúci krok, vlastník.
- **Document** + **DocumentVersion** — verzia, dátum, autor, stav:
  `aktualna | historicka | nepotvrdena | nahradena`. Staré verzie sa archivujú,
  nikdy nemažú. Platnosť potvrdzuje človek (`confirmed_by`, `confirmed_at`).
  Typ dokumentu z číselníka (PD, rozpočet, LV, zmluva, stanovisko, …).
- **InboxItem** — surový vstup (text, e-mail, súbor) + AI klasifikácia
  (projekt, typ, deadline, istota) + flag `nepotvrdene`.
- **Question** — kto položil, komu, kedy, prečo, presné znenie, viazané na
  projekt/dokument, termín na odpoveď, stav.
- **Answer** — kto odpovedal, kedy, presné znenie, zdroj, záväznosť:
  `zavazne | pracovne | neformalne`.
- **Decision** — čo sa zvolilo, kto schválil, prečo, odkazy na otázky/odpovede.
- **Task** — úloha sa nedá uzavrieť bez dôkazu: pole „požadovaný dôkaz",
  uzatvára sa priložením prílohy alebo odkazu na dokument. Priorita, termín,
  zodpovedná osoba, väzba na projekt.
- **Risk** — dopad, pravdepodobnosť, mitigácia, stav, väzba na projekt.
- **Gate** — kontrolná brána medzi fázami s checklistom podmienok; projekt
  nepostúpi do ďalšej fázy, kým brána neprejde (kto skontroloval, kedy).
- **Discrepancy** — nesúlad z krížovej kontroly: popis, zdrojové dokumenty
  s citáciami, priradená osoba, stav.
- **AuditLog** — kto čo kedy zmenil (príprava na firemnú verziu).

### Fázy projektu (12)

1. prvotný screening, 2. rozhodnutie o príprave, 3. zber podkladov,
4. technická a finančná kontrola, 5. príprava žiadosti, 6. podanie,
7. schválenie a zmluva, 8. VO, 9. realizácia, 10. platby a monitorovanie,
11. ukončenie, 12. udržateľnosť.

## AI vrstva

Každý AI výstup má stav `navrhnute | schvalene | upravene | zamietnute` —
nič sa nezapíše ako fakt bez človeka; zároveň sa tým zbierajú dáta na budúce
učenie. AI beží v queue joboch, výsledky sa zobrazujú ako návrhy na schválenie.

1. **Inteligentný inbox** — vstup → AI určí projekt, typ (dokument/otázka/
   úloha/riziko), deadline, mieru istoty. Nízka istota = `nepotvrdene` +
   návrh otázky na doplnenie. Structured output (JSON schema).
2. **Krížové kontroly** — PM vyberie 2+ dokumenty projektu → AI ich porovná
   a vytvorí návrhy `Discrepancy` s citáciami zdrojov.
3. **Návrhy otázok a e-mailov** — AI naformuluje otázku pre klienta/úrad,
   e-mail so zoznamom chýbajúcich podkladov, checklist podľa výzvy.
   Človek vždy schvaľuje pred použitím.
4. **Priorizácia** — denný scheduled job zhodnotí blízkosť termínov,
   blokátory, finančný dopad a závislosti → „Dnešné priority" na dashboarde
   + návrh, čo sa dá robiť paralelne počas čakania.

## Obrazovky (podľa mockupu)

- **Dashboard** — 4 karty (aktívne projekty, blížiace sa termíny, otvorené
  riziká, čaká sa na klienta), portfólio projektov, dnešné priority,
  auditná história, modál „Nová úloha".
- **Dnes** — denný pohľad na úlohy a termíny.
- **Inbox** — fronta vstupov + AI klasifikácia na schválenie.
- **Projekty** → projektový priestor so záložkami: Prehľad (zdroj pravdy:
  chýbajúce podklady, otvorené otázky, kontrolná brána), Dokumenty,
  Požiadavky, Úlohy, Riziká, Fázy.
- **Dokumenty**, **Rozhodnutia**, **Riziká**, **Nastavenia**.
- Administrácia = Orchid (samostatná sekcia /admin).

Academy a globálne vyhľadávanie: v2.

## Poradie výstavby

1. **Skeleton** — Laravel + Orchid + auth + celý dátový model (migrácie,
   modely, vzťahy) + seed demo dát (Malé Hoste, Hronská Dúbrava, …).
2. **Jadro UI** — dashboard + projektový priestor + CRUD bez AI.
3. **Dokumenty** — upload, verziovanie, stavy, archív, potvrdzovanie platnosti.
4. **Proces** — otázky/odpovede/rozhodnutia, úlohy s dôkazmi, kontrolné brány.
5. **AI** — inbox → návrhy textov → priorizácia → krížové kontroly.

## Testovanie

Pest feature testy pre doménové pravidlá: úloha sa nedá uzavrieť bez dôkazu,
brána blokuje postup fázy, nahradenie verzie dokumentu archivuje starú,
AI služby mockované cez `Http::fake()` / SDK test doubles.

## Mimo rozsahu v1

Multi-user tím a roly, klientsky prístup, recenzie/hodnotenia, mikrotransakcie,
mobilná verzia, automatické učenie AI (len zber dát), Academy, notifikácie
e-mailom.
