<?php

namespace Database\Seeders;

use App\Enums\InboxItemStatus;
use App\Enums\InboxSource;
use App\Models\Client;
use App\Models\Decision;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\DocumentVersion;
use App\Models\Gate;
use App\Models\InboxItem;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Question;
use App\Models\Risk;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoSeeder extends Seeder
{
    /**
     * Seed the mockup demo portfolio.
     */
    public function run(): void
    {
        $denis = User::query()->firstOrCreate(
            ['email' => 'denis@eurofond.test'],
            [
                'name' => 'Denis',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );

        $clientMaleHoste = Client::create([
            'name' => 'Obec Malé Hoste',
            'type' => 'obec',
        ]);

        $clientTornala = Client::create([
            'name' => 'Mesto Tornaľa',
            'type' => 'obec',
        ]);

        $clientHronskaDubrava = Client::create([
            'name' => 'Obec Hronská Dúbrava',
            'type' => 'obec',
        ]);

        $clientGalantskyKastiel = Client::create([
            'name' => 'Mesto Galanta',
            'type' => 'obec',
        ]);

        $projectMaleHoste = Project::create([
            'code' => 'PRJ-001',
            'name' => 'Malé Hoste',
            'client_id' => $clientMaleHoste->id,
            'call_name' => 'Obnova verejných budov',
            'budget_total' => 185000,
            'grant_requested' => 175000,
            'phase' => 4,
            'status_label' => 'Čaká na PD',
            'health' => 'dobre',
            'next_deadline' => today()->addDays(4),
            'main_blocker' => 'Čaká sa na doplnenie projektovej dokumentácie.',
            'next_step' => 'Skontrolovať PD a doplniť podklady.',
            'owner_id' => $denis->id,
        ]);

        $projectTornala = Project::create([
            'code' => 'PRJ-002',
            'name' => 'Tornaľa',
            'client_id' => $clientTornala->id,
            'call_name' => 'Modernizácia verejného osvetlenia',
            'budget_total' => 240000,
            'grant_requested' => 220000,
            'phase' => 10,
            'status_label' => 'Monitoring',
            'health' => 'dobre',
            'next_deadline' => today()->addDays(30),
            'main_blocker' => null,
            'next_step' => 'Pripraviť monitorovaciu správu.',
            'owner_id' => $denis->id,
        ]);

        $projectHronskaDubrava = Project::create([
            'code' => 'PRJ-005',
            'name' => 'Hronská Dúbrava',
            'client_id' => $clientHronskaDubrava->id,
            'call_name' => 'Rekonštrukcia kultúrneho domu',
            'budget_total' => 310000,
            'grant_requested' => 290000,
            'phase' => 9,
            'status_label' => 'Rozpočet / audit',
            'health' => 'stredne',
            'next_deadline' => today()->addDays(11),
            'main_blocker' => 'Rozpočet vyžaduje audit oprávnenosti výdavkov.',
            'next_step' => 'Vypracovať rozpočtové zdôvodnenie.',
            'owner_id' => $denis->id,
        ]);

        $projectGalantskyKastiel = Project::create([
            'code' => 'PRJ-006',
            'name' => 'Galantský kaštieľ',
            'client_id' => $clientGalantskyKastiel->id,
            'call_name' => 'Obnova kultúrnej pamiatky',
            'budget_total' => 420000,
            'grant_requested' => 400000,
            'phase' => 5,
            'status_label' => 'Príprava žiadosti',
            'health' => 'dobre',
            'next_deadline' => today()->addDays(21),
            'main_blocker' => null,
            'next_step' => 'Dokončiť prípravu žiadosti o NFP.',
            'owner_id' => $denis->id,
        ]);

        $this->seedMaleHosteDetail($projectMaleHoste, $denis);
        $this->seedHronskaDubravaDetail($projectHronskaDubrava, $denis);
        $this->seedAdditionalTasksAndRisks($projectTornala, $projectGalantskyKastiel, $projectMaleHoste);
        $this->seedInbox($denis, $projectMaleHoste, $projectTornala, $projectHronskaDubrava, $projectGalantskyKastiel);
    }

    private function seedMaleHosteDetail(Project $project, User $denis): void
    {
        $pdType = DocumentType::where('slug', 'pd')->firstOrFail();
        $rozpocetType = DocumentType::where('slug', 'rozpocet')->firstOrFail();

        $pdDocument = Document::create([
            'project_id' => $project->id,
            'document_type_id' => $pdType->id,
            'title' => 'Projektová dokumentácia',
        ]);

        $pdV1 = DocumentVersion::create([
            'document_id' => $pdDocument->id,
            'version_label' => 'v1.0',
            'issued_at' => Carbon::parse('2026-06-15'),
            'author' => 'Ing. arch. Peter Kováč',
            'uploaded_by' => $denis->id,
        ]);
        $pdV1->activate($denis);

        $pdV2 = DocumentVersion::create([
            'document_id' => $pdDocument->id,
            'version_label' => 'v1.2',
            'issued_at' => Carbon::parse('2026-07-20'),
            'author' => 'Ing. arch. Peter Kováč',
            'uploaded_by' => $denis->id,
        ]);
        $pdV2->activate($denis);

        $rozpocetDocument = Document::create([
            'project_id' => $project->id,
            'document_type_id' => $rozpocetType->id,
            'title' => 'Rozpočet',
        ]);

        $rozpocetV3 = DocumentVersion::create([
            'document_id' => $rozpocetDocument->id,
            'version_label' => 'v3.0',
            'issued_at' => Carbon::parse('2026-07-25'),
            'author' => 'Ing. Jana Slušná',
            'uploaded_by' => $denis->id,
        ]);
        $rozpocetV3->activate($denis);

        $questionMaleHoste = Question::create([
            'project_id' => $project->id,
            'document_id' => $pdDocument->id,
            'asked_by' => 'Denis',
            'asked_to' => 'Obec Malé Hoste',
            'asked_at' => Carbon::parse('2026-07-22 09:00:00'),
            'reason' => 'Overenie súladu rozpočtu s aktualizovanou PD.',
            'body' => 'Je rozpočet v súlade s usmernením?',
            'due_at' => Carbon::parse('2026-08-05'),
            'created_by' => $denis->id,
        ]);

        ProjectTask::create([
            'project_id' => $project->id,
            'title' => 'Skontrolovať PD a doplniť podklady',
            'assignee_id' => $denis->id,
            'priority' => 'vysoka',
            'due_at' => today()->subDays(3),
            'status' => 'otvorena',
            'required_evidence' => 'Checklist podkladov',
        ]);

        ProjectTask::create([
            'project_id' => $project->id,
            'title' => 'Zaslať doplnené technické listy obci',
            'assignee_id' => $denis->id,
            'priority' => 'stredna',
            'due_at' => today(),
            'status' => 'caka',
            'required_evidence' => 'Potvrdenie o odoslaní',
        ]);

        Risk::create([
            'project_id' => $project->id,
            'title' => 'Stará PD nesedí s novým rozpočtom',
            'description' => 'Pôvodná projektová dokumentácia neobsahuje položky z aktualizovaného rozpočtu.',
            'impact' => 'vysoky',
            'likelihood' => 'stredny',
        ]);

        Risk::create([
            'project_id' => $project->id,
            'title' => 'Chýbajúce stanovisko k verejnému obstarávaniu',
            'description' => 'Stanovisko VO nie je doložené pre plánovaný rozsah prác.',
            'impact' => 'stredny',
            'likelihood' => 'nizky',
        ]);

        Decision::create([
            'project_id' => $project->id,
            'question_id' => $questionMaleHoste->id,
            'body' => 'Rozpočet je v súlade s aktualizovaným usmernením poskytovateľa.',
            'approved_by' => 'Ing. Jana Slušná',
            'approved_at' => now()->subDays(2),
            'rationale' => 'Kontrola rozpočtových položiek potvrdila súlad s platným usmernením k oprávnenosti výdavkov.',
            'recorded_by' => $denis->id,
        ]);
    }

    private function seedHronskaDubravaDetail(Project $project, User $denis): void
    {
        $gate = Gate::create([
            'project_id' => $project->id,
            'phase' => 9,
            'name' => 'Brána 3 – Rozpočet a oprávnenosť',
        ]);

        $gate->items()->create([
            'label' => 'Rozpočet odsúhlasený s poskytovateľom',
            'is_met' => true,
        ]);

        $gate->items()->create([
            'label' => 'Oprávnenosť výdavkov overená auditom',
            'is_met' => true,
        ]);

        $gate->pass($denis);

        $questionEnergHodnotenie = Question::create([
            'project_id' => $project->id,
            'asked_by' => 'Denis',
            'asked_to' => 'Obec Hronská Dúbrava',
            'asked_at' => Carbon::parse('2026-07-24 10:30:00'),
            'reason' => 'Podklad pre uzavretie brány fázy 9.',
            'body' => 'Je energetické hodnotenie priložené k žiadosti o platbu?',
            'due_at' => Carbon::parse('2026-08-10'),
            'created_by' => $denis->id,
        ]);

        $questionZmluva = Question::create([
            'project_id' => $project->id,
            'asked_by' => 'Denis',
            'asked_to' => 'Obec Hronská Dúbrava',
            'asked_at' => Carbon::parse('2026-07-26 14:00:00'),
            'reason' => 'Kontrola súladu rozpočtu so zmluvou o dielo.',
            'body' => 'Súhlasí rozpočet s podpísanou zmluvou o dielo?',
            'due_at' => Carbon::parse('2026-08-12'),
            'created_by' => $denis->id,
        ]);

        ProjectTask::create([
            'project_id' => $project->id,
            'title' => 'Vypracovať rozpočtové zdôvodnenie',
            'assignee_id' => $denis->id,
            'priority' => 'vysoka',
            'due_at' => today()->addDay(),
            'status' => 'caka',
            'required_evidence' => 'Zdôvodňujúci dokument k rozpočtu',
        ]);

        Risk::create([
            'project_id' => $project->id,
            'title' => 'Nesúlad rozpočtu s aktuálnymi trhovými cenami',
            'description' => 'Ceny materiálov od podania žiadosti výrazne vzrástli.',
            'impact' => 'vysoky',
            'likelihood' => 'stredny',
        ]);

        Risk::create([
            'project_id' => $project->id,
            'title' => 'Meškanie verejného obstarávania ovplyvní harmonogram',
            'description' => 'VO na dodávateľa stavebných prác ešte nebolo ukončené.',
            'impact' => 'stredny',
            'likelihood' => 'stredny',
        ]);

        Decision::create([
            'project_id' => $project->id,
            'question_id' => $questionEnergHodnotenie->id,
            'body' => 'Energetické hodnotenie je priložené k žiadosti o platbu.',
            'approved_by' => 'Denis',
            'approved_at' => now()->subDay(),
            'rationale' => 'Dokument bol overený a zodpovedá požiadavkám poskytovateľa.',
            'recorded_by' => $denis->id,
        ]);

        Decision::create([
            'project_id' => $project->id,
            'question_id' => $questionZmluva->id,
            'body' => 'Rozpočet súhlasí s podpísanou zmluvou o dielo.',
            'approved_by' => 'Ing. Peter Novák',
            'approved_at' => now()->subHours(6),
            'rationale' => 'Porovnanie položiek rozpočtu so zmluvou nepreukázalo žiadny rozdiel.',
            'recorded_by' => $denis->id,
        ]);
    }

    private function seedAdditionalTasksAndRisks(
        Project $projectTornala,
        Project $projectGalantskyKastiel,
        Project $projectMaleHoste,
    ): void {
        ProjectTask::create([
            'project_id' => $projectTornala->id,
            'title' => 'Pripraviť monitorovaciu správu',
            'priority' => 'stredna',
            'due_at' => today()->addDays(7),
            'status' => 'caka',
            'required_evidence' => 'Podklady k monitorovaciemu obdobiu',
        ]);

        ProjectTask::create([
            'project_id' => $projectGalantskyKastiel->id,
            'title' => 'Podpísať zmluvu o dielo',
            'priority' => 'vysoka',
            'due_at' => today()->addDays(12),
            'status' => 'caka',
        ]);

        Risk::create([
            'project_id' => $projectTornala->id,
            'title' => 'Zmena podmienok monitorovacej správy',
            'description' => 'Poskytovateľ avizoval zmenu formulára monitorovacej správy.',
            'impact' => 'stredny',
            'likelihood' => 'nizky',
        ]);

        Risk::create([
            'project_id' => $projectTornala->id,
            'title' => 'Chýbajúce podklady k platbe',
            'description' => 'Obec nedoložila všetky faktúry k žiadosti o platbu.',
            'impact' => 'vysoky',
            'likelihood' => 'stredny',
        ]);

        Risk::create([
            'project_id' => $projectGalantskyKastiel->id,
            'title' => 'Verejné obstarávanie môže byť napadnuté',
            'description' => 'Neúspešný uchádzač avizoval možnosť podania námietky.',
            'impact' => 'vysoky',
            'likelihood' => 'stredny',
        ]);

        Risk::create([
            'project_id' => $projectGalantskyKastiel->id,
            'title' => 'Rozpočet projektu prekračuje pôvodný odhad',
            'description' => 'Aktualizovaný rozpočet je vyšší než pôvodne schválená suma.',
            'impact' => 'stredny',
            'likelihood' => 'stredny',
        ]);
    }

    private function seedInbox(
        User $denis,
        Project $projectMaleHoste,
        Project $projectTornala,
        Project $projectHronskaDubrava,
        Project $projectGalantskyKastiel,
    ): void {
        $items = [
            [
                'source' => InboxSource::Email,
                'raw_content' => 'Obec Malé Hoste posiela aktualizovaný rozpočet v prílohe.',
                'status' => InboxItemStatus::Nove,
                'unconfirmed' => true,
                'suggested_project_id' => $projectMaleHoste->id,
                'suggested_type' => 'rozpocet',
            ],
            [
                'source' => InboxSource::Telefonat,
                'raw_content' => 'Starostka Tornale volala kvôli termínu monitorovacej správy.',
                'status' => InboxItemStatus::Nove,
                'unconfirmed' => true,
                'suggested_project_id' => $projectTornala->id,
            ],
            [
                'source' => InboxSource::Poznamka,
                'raw_content' => 'Poznámka z porady: Hronská Dúbrava potrebuje doplniť energetický certifikát.',
                'status' => InboxItemStatus::Nove,
                'unconfirmed' => true,
                'suggested_project_id' => $projectHronskaDubrava->id,
            ],
            [
                'source' => InboxSource::Email,
                'raw_content' => 'Galantský kaštieľ - dodávateľ žiada predĺženie termínu verejného obstarávania.',
                'status' => InboxItemStatus::Nove,
                'unconfirmed' => true,
                'suggested_project_id' => $projectGalantskyKastiel->id,
            ],
            [
                'source' => InboxSource::Email,
                'raw_content' => 'Nová výzva na obnovu verejného osvetlenia - treba preveriť oprávnenosť pre Tornaľu.',
                'status' => InboxItemStatus::Nove,
                'unconfirmed' => true,
            ],
            [
                'source' => InboxSource::Telefonat,
                'raw_content' => 'Telefonát od projektanta k PD pre Malé Hoste - žiada spresnenie rozsahu prác.',
                'status' => InboxItemStatus::Nove,
                'unconfirmed' => false,
                'suggested_project_id' => $projectMaleHoste->id,
            ],
            [
                'source' => InboxSource::Poznamka,
                'raw_content' => 'Interná poznámka: skontrolovať čerpanie rozpočtu pred auditom.',
                'status' => InboxItemStatus::Klasifikovane,
                'unconfirmed' => false,
                'suggested_project_id' => $projectHronskaDubrava->id,
                'suggested_type' => 'rozpocet',
            ],
            [
                'source' => InboxSource::Email,
                'raw_content' => 'Potvrdenie prijatia žiadosti o platbu od poskytovateľa.',
                'status' => InboxItemStatus::Schvalene,
                'unconfirmed' => false,
                'suggested_project_id' => $projectTornala->id,
            ],
        ];

        foreach ($items as $offset => $item) {
            InboxItem::create([
                ...$item,
                'created_by' => $denis->id,
                'created_at' => now()->subHours(count($items) - $offset),
            ]);
        }
    }
}
