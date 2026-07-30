<?php

use App\Enums\ProjectPhase;
use App\Enums\DocumentVersionStatus;
use App\Enums\InboxItemStatus;
use App\Enums\InboxSource;
use App\Enums\QuestionStatus;
use App\Enums\AnswerBindingness;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\RiskLevel;
use App\Enums\RiskStatus;
use App\Enums\GateStatus;
use App\Enums\DiscrepancyStatus;
use App\Enums\AiSuggestionKind;
use App\Enums\AiSuggestionStatus;
use App\Enums\ProjectHealth;

test('project has 12 phases with slovak labels', function () {
    expect(ProjectPhase::cases())->toHaveCount(12)
        ->and(ProjectPhase::Screening->label())->toBe('Prvotný screening')
        ->and(ProjectPhase::Udrzatelnost->value)->toBe(12);
});

test('document version statuses', function () {
    expect(array_column(DocumentVersionStatus::cases(), 'value'))
        ->toBe(['aktualna', 'historicka', 'nepotvrdena', 'nahradena']);
});

test('project health enum has correct values and labels', function () {
    expect(array_column(ProjectHealth::cases(), 'value'))
        ->toBe(['dobre', 'stredne', 'riziko'])
        ->and(ProjectHealth::Dobre->label())->toBe('Dobré')
        ->and(ProjectHealth::Stredne->label())->toBe('Stredné')
        ->and(ProjectHealth::Riziko->label())->toBe('Riziko');
});

test('inbox item status enum has correct values and labels', function () {
    expect(array_column(InboxItemStatus::cases(), 'value'))
        ->toBe(['nove', 'klasifikovane', 'schvalene', 'zamietnute'])
        ->and(InboxItemStatus::Nove->label())->toBe('Nové')
        ->and(InboxItemStatus::Zamietnute->label())->toBe('Zamietnuté');
});

test('inbox source enum has correct values', function () {
    expect(array_column(InboxSource::cases(), 'value'))
        ->toBe(['email', 'poznamka', 'subor', 'telefonat', 'ine']);
});

test('question status enum has correct values', function () {
    expect(array_column(QuestionStatus::cases(), 'value'))
        ->toBe(['otvorena', 'zodpovedana', 'uzavreta']);
});

test('answer bindingness enum has correct values and labels', function () {
    expect(array_column(AnswerBindingness::cases(), 'value'))
        ->toBe(['zavazne', 'pracovne', 'neformalne'])
        ->and(AnswerBindingness::Zavazne->label())->toBe('Záväzné')
        ->and(AnswerBindingness::Pracovne->label())->toBe('Pracovné');
});

test('task priority enum has correct values', function () {
    expect(array_column(TaskPriority::cases(), 'value'))
        ->toBe(['nizka', 'stredna', 'vysoka', 'blokator']);
});

test('task status enum has correct values and labels', function () {
    expect(array_column(TaskStatus::cases(), 'value'))
        ->toBe(['otvorena', 'caka', 'hotova'])
        ->and(TaskStatus::Caka->label())->toBe('Čaká');
});

test('risk level enum has correct values', function () {
    expect(array_column(RiskLevel::cases(), 'value'))
        ->toBe(['nizky', 'stredny', 'vysoky']);
});

test('risk status enum has correct values and labels', function () {
    expect(array_column(RiskStatus::cases(), 'value'))
        ->toBe(['otvorene', 'mitigovane', 'uzavrete', 'prejavilo_sa'])
        ->and(RiskStatus::PrejaviloSa->label())->toBe('Prejavilo sa');
});

test('gate status enum has correct values', function () {
    expect(array_column(GateStatus::cases(), 'value'))
        ->toBe(['cakajuca', 'prejdena', 'zamietnuta']);
});

test('discrepancy status enum has correct values', function () {
    expect(array_column(DiscrepancyStatus::cases(), 'value'))
        ->toBe(['otvoreny', 'vyrieseny', 'zamietnuty']);
});

test('ai suggestion kind enum has correct values and labels', function () {
    expect(array_column(AiSuggestionKind::cases(), 'value'))
        ->toBe(['inbox_klasifikacia', 'krizova_kontrola', 'navrh_textu', 'priorizacia'])
        ->and(AiSuggestionKind::InboxKlasifikacia->label())->toBe('Inbox klasifikácia')
        ->and(AiSuggestionKind::KrizovaKontrola->label())->toBe('Krížová kontrola')
        ->and(AiSuggestionKind::NavrhTextu->label())->toBe('Návrh textu')
        ->and(AiSuggestionKind::Priorizacia->label())->toBe('Priorizácia');
});

test('ai suggestion status enum has correct values and labels', function () {
    expect(array_column(AiSuggestionStatus::cases(), 'value'))
        ->toBe(['navrhnute', 'schvalene', 'upravene', 'zamietnute'])
        ->and(AiSuggestionStatus::Navrhnute->label())->toBe('Navrhnuté');
});
