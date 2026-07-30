# Projekt – EUROFOND OS

> Má byť pracovný operačný systém pre ľudí, ktorí pripravujú eurofondové, dotačné a grantové projekty.

---

## Čo rieši tento projekt?

Dnes projektový manažér pracuje v niekoľkých nespojených svetoch. Dokumenty má v priečinkoch, termíny v kalendári, otázky v e-mailoch, úlohy v poznámkach, rozhodnutia v telefonátoch a aktuálny stav projektu často iba vo vlastnej hlave. Keď má päť, desať alebo dvadsať projektov, vzniká chaos.

Najväčší problém nie je nedostatok informácií, ale neschopnosť rýchlo určiť, ktoré informácie sú aktuálne, čo je potvrdené, čo je iba predpoklad, čo chýba a čo treba spraviť ako prvé.

**Cieľom je vybudovať systém, ktorý z toho chaosu vytvorí jeden zdroj pravdy pre celé portfólio projektov.**

Keď projektový manažér otvorí aplikáciu, nemá vidieť iba zoznam úloh. Má okamžite vidieť:

- ktoré projekty sú aktívne
- ktorý má najbližší deadline
- ktorý je blokovaný a prečo
- na aký dokument alebo odpoveď čaká
- kto je zodpovedný
- čo môže spôsobiť zamietnutie, krátenie alebo omeškanie
- najbližší krok a ďalší krok

---

## Na čom je systém postavený

Základom nie je AI. Základom je správne zachytená logika práce projektového manažéra.

**Projekt** hovorí, čo sa pripravuje, pre koho, z akej výzvy, za koľko a v akej fáze.

**Dokument** predstavuje dôkaz. PD, rozpočet, energetický certifikát, LV, zmluva, faktúra alebo stanovisko nemajú rovnakú úlohu. Každý dokument musí mať verziu, dátum, autora, stav *(vzťah k projektu, tiež používateľa)* [?].

**Otázka** zachytáva nejasnosť. AI sa pýta pre jeho pochopenie. Aj moje otázky zapisovať nasledovne:

1. kým bola položená
2. kedy
3. prečo
4. na aký problém sa viaže

**Odpoveď** zaznamenáva, kto ju poskytol, čo presne povedal a či ide o záväzné stanovisko, pracovnú informáciu alebo neformálnu komunikáciu.

> Systém by mohol fungovať tak, že firmy, obce, projektoví manažéri…
> → pri väčšom množstve užívateľov by to fungovalo a zbieralo informácie
> → docieli sa tým, že AI-ko sa bude môcť automaticky učiť, bez námahy používateľa

**Rozhodnutie** zaznamenáva, čo sa na základe informácií zvolilo, kto to schválil a prečo.

**Úloha** určuje, čo treba vykonať, kto to má spraviť, dokedy a aký dôkaz je potrebný na jej uzavretie.

**Riziko** vysvetľuje, čo sa môže pokaziť, aký bude dôsledok a čo ho odstráni.

**Kontrolná brána** rozhoduje, či projekt môže postúpiť do ďalšej fázy.

---

## ZÁKLADNÁ LOGIKA SYSTÉMU

```
PORTFÓLIO → INBOX → PROJEKT → DOKUMENTY → OTÁZKY → ROZHODNUTIA → ÚLOHY
→ KONTROLY → BRÁNY → PODANIE ALEBO IMPLEMENTÁCIA
```

---

## Ako sa do systému dostane nový projekt?

Všetko začína vstupom. Môže prísť ako:

- e-mail od klienta
- projektový zámer
- nová výzva
- dokumentácia
- telefonická informácia
- požiadavka šéfa
- poznámka projektového manažéra

Vstup najprv ide do centrálneho in-boxu. Systém sa snaží určiť:

- ku ktorému klientovi patrí
- ku ktorému projektu patrí
- či ide o nový projekt
- či je to dokument, otázka, úloha alebo riziko
- či z toho vzniká deadline
- či je potrebná okamžitá reakcia

Nejasné informácie sa nemajú automaticky zapísať ako fakt. Majú ostať označené ako nepotvrdené alebo majú vytvoriť otázku na doplnenie.

> **ZÁSADNÝ PRINCÍP:** systém nesmie zameniť predpoklad za potvrdenú skutočnosť.

---

## Ako vyzerá projektový priestor

Každý projekt má vlastný pracovný priestor.

- Na vrchu je stručný „zdroj pravdy".
- Pod tým sú jednotlivé registre.

| Register | | |
|---|---|---|
| názov projektu | rozpočet | aktuálna fáza |
| žiadateľ alebo prijímateľ | požadovaný príspevok | najbližší deadline |
| výzva | stav projektu | hlavný blokátor |
| | | nasledujúci krok |
| | | osoby zodpovedné za jednotlivé oblasti |

PM nemusí každý deň študovať celý spis. Najprv vidí zhrnutie a až potom si otvorí konkrétny problém.

> **Myšlienka:** V danej aplikácii by napr. ľudia vedeli ohodnotiť (na základe recenzií, ktoré budú mať v aplikácii na profile).
> Firmy a mestá [?] budú mať platenejší model, lebo budú pasívni užívatelia…
> Aké by mali mať výhody – treba vymyslieť.

---

## Dokumenty ako zdroj pravdy

Jedna z najdôležitejších funkcií je správa dokumentov a ich platností.

V eurofondových projektoch často existuje:

- stará a nová PD
- viac verzií rozpočtu
- rozpracovaný a finálny energetický dokument
- neplatné alebo nahradené stanovisko
- návrh zmluvy a podpísaná zmluva

Systém musí jasne ukazovať:

- ktorá verzia je aktuálna
- ktorá je historická
- ktorá je nepotvrdená
- ktorá bola nahradená
- podľa ktorej sa smie pracovať
- kto jej platnosť potvrdil

Staré dokumenty sa nemažú. Presunú sa do archívu, aby zostala história, ale nesmú sa použiť na nové závery. Archivujú sa.

> Môžu tam byť rôzne mikrotransakcie – za vloženie v aplikácii, za body, aby PM boli odmeňovaní…

**Príklad z praxe:** Robil som projekt pre Malé Hoste, kde vznikla presne situácia, ktorú riešime: stará PD a PEH sa archivujú, čaká sa na novú PD a aktuálny rozpočet sa následne musí voči novej dokumentácii znovu overiť.

---

## Krížové kontroly

EUROFOND OS nemá iba skladovať dokumenty. Má sledovať, či sa navzájom nebijú.

**Napríklad:**

- PD uvádza päť solárnych kolektorov
- energetické hodnotenie počíta s piatimi
- rozpočet obsahuje iba zásobník a žiadne kolektory

Systém má vytvoriť nesúlad:
> Technológia je v PD a energetickom výpočte, ale nie je kompletne zahrnutá v rozpočte.

**Alebo:**

- žiadosť sľubuje riadený vstup
- realizácia obsahuje bránu a kameru
- chýba dôkaz, že ide skutočne o riadený vstup

Systém nevynesie právne alebo technické rozhodnutia bez človeka. Upozorní na konflikt, uloží zdroje, vytvorí otázku a priradí riešenie konkrétnej osobe.

> → Takto sa z dokumentového archívu stáva aktívny kontrolný systém.

---

## Otázky, odpovede a rozhodnutia

Toto je jedna z častí, ktorou sa produkt odlišuje od bežného projektového manažmentu.

**Každá dôležitá otázka má obsahovať:**

- dátum
- kto ju položil
- komu
- presné znenie
- dôvod otázky
- projekt alebo dokument, ktorého sa týka
- termín na odpoveď
- stav

**Keď príde odpoveď, systém zaznamená:**

- kto odpovedal
- kedy
- presné znenie / dôveryhodný prepis
- zdroj odpovede
- či je odpoveď záväzná
- aké rozhodnutie alebo úloha z nej vzniká

> → To znamená, že o 3 mesiace vieme zrekonštruovať: kto rozhodol, na základe čoho, aké informácie mal, kedy a prečo sa projekt vyvíjal daným smerom.

---

## Úloha nie je to-do list

Úloha nie je dokončená len preto, že ju niekto označil za dokončenú. Úloha musí byť ukončená výsledkom alebo dôkazom.

**Príklady:**

- „vyžiadať aktuálny LV" sa uzavrie až po priložení výpisu
- „overiť úhradu faktúry" sa uzavrie po priložení bankového výpisu
- „odsúhlasiť metodiku" sa uzavrie protokolom a fotodokumentáciou

Tým sa zníži pocit, že je niečo hotové, hoci chýba dôkaz.

---

## Sekvencie a dôležitosť

Systém má rozlišovať medzi:

- urgentnou úlohou
- dôležitou úlohou
- blokátorom
- vyčkávacím stavom [?]

Poradie sa neurčuje „na dokument". Hodnotí sa:

- blízkosť termínu
- finančný dopad
- pravdepodobnosť zamietnutia alebo krátenia
- závislosť ďalších úloh
- čas potrebný na úpravu
- osoba, od ktorej sa čaká odpoveď

Ak projekt čaká na novú PD, systém má zároveň ukázať, čo možno robiť paralelne – vlastníctvo, VO / povolenia, audit rozpočtu alebo príprava checklistu.

---

## Kontrolné brány

Každý projekt prechádza fázami:

1. prvotný screening
2. rozhodnutie, či má zmysel projekt pripravovať
3. zber podkladov
4. technická a finančná kontrola
5. príprava žiadosti
6. podanie
7. schválenie a zmluva
8. VO → môže byť žiadané aj pred splnením podmienok žiadosti
9. realizácia
10. platby a monitorovanie
11. ukončenie
12. udržateľnosť

**Projekt nemá byť označený ako pripravený na podanie, ak:**

- nie je jasná vlastnícka / spisová situácia [?]
- chýba povinná príloha
- rozpočet nesedí s PD
- nie je vyriešené vlastníctvo
- chýba požadované povolenie
- nie je posúdené financovanie

**Projekt nemá byť označený ako pripravený na realizáciu, ak:**

- chýba zmluva alebo dohoda
- nie je preukázaný vykonávateľ
- nie je ukončené VO
- publicita nie je doriešená

> Brána teda znamená stopku pre PM predtým, ako bude pokračovať s rozpracovaným projektom.

---

## Úloha AI

AI je pracovná vrstva nad týmto systémom.

**Má pomáhať:**

| | |
|---|---|
| prečítať vstup | pripraviť otázky |
| vytiahnuť podmienky | navrhnúť e-mail |
| pripraviť check-list | zostaviť [?] |
| porovnať dokumenty | vytvoriť príznaky poradia |
| nájsť nezrovnalosti | upozorniť na chýbajúce dôkazy |

**AI nerobí nevratné alebo záväzné rozhodnutia.**

> **Princíp:** AI navrhne → človek skontroluje / schváli → systém zaznamená rozhodnutie a dôkaz.
> AI sa môže učiť z procesných relácií a spätnej väzby používateľa.

---

## Ako sa bude systém učiť?

Systém sa má učiť z reálnej práce PM:

- aké návrhy AI vytvorila
- čo človek opravil (→ AI sa môže učiť, prečo)
- prečo to opravil
- aký text radšej použil / vytvoril
- či bol finálny výber ako vykonaný
- či bola odpoveď považovaná za akceptovanú
- či kontrola našla chybu
- ktoré riziko sa skutočne prejavilo

**Postupne vzniká knižnica:**

- typických problémov
- kontrolných postupov
- vzorových otázok
- kvalitných odpovedí
- checklistov podľa výziev
- pravidiel podľa poskytovateľov
- historických prípadov

> To je konkurenčná výhoda: nahromadené a overené eurofondové know-how v procesnej forme.

---

## Firemná verzia

Prvá verzia môže fungovať pre jedného PM. **Cieľom je však firemný systém.**

**Firma bude vidieť:**

- spoločné portfólio
- vlastníkov projektov
- pridelených kolegov
- interné roly
- schvaľovanie výstupov
- prehľad vyťaženosti
- manažérsky dashboard
- audit toho, kto čo zmenil
- možnosť odovzdať projekt inému pracovníkovi bez straty kontextu

**Šéf firmy nebude musieť každému telefonovať. Uvidí:**

- ktoré projekty sú v riziku
- kde sa čaká na klienta
- kde sa blíži termín
- ktorý pracovník je preťažený
- ktoré rozhodnutia nemajú dôkaz
- lokálne podklady [?]

> Dá sa z toho spraviť aj verzia na mobil.

---

## Klientska vrstva

Neskôr môže mať klient obmedzený prístup → spoľahlivé riešenie napr. pre obec alebo firmu.

Projektant / PM tak vie riadiť viac projektov naraz, s menším chaosom, menším rizikom a chybovosťou a s menšou závislosťou od toho, čo si pamätá on alebo jeho kolegovia.

**Obec alebo firma uvidí iba to, čo sa jej týka:**

- zoznam požadovaných dokumentov
- termíny
- otvorené otázky
- stav projektu

Namiesto 10 mailov dostane jasný zoznam:

> „Toto chýba, toto treba podpísať, toto sú termíny a bez toho projekt nepokročí."

---

*[?] = miesto, kde bol rukopis nejednoznačný – prosím over.*
