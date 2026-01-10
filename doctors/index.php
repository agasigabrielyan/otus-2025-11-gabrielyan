<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/bitrix/header.php";
/**
 * @var $APPLICATION
 */

$APPLICATION->SetTitle("Доктора (Приемный покой)");

use OtusApp\OtusDoctors\MyOtusDoctorsIblockCreation;
use OtusApp\OtusModels\Lists\DoctorsPropertyValuesTable as DoctorsTable;
use Bitrix\Iblock\ElementTable;

\Bitrix\Main\Page\Asset::getInstance()->addCss("/doctors/otus-doctors.css");

$doctorsIblockId = MyOtusDoctorsIblockCreation::migrateAndFillDemoData();
$doctorId = isset($_GET['DOCTOR_ID']) ? (int)$_GET['DOCTOR_ID'] : 0;

$proceduresIblock = \CIBlock::GetList([], ['CODE' => 'procedures'])->Fetch();
$proceduresIblockId = (int)$proceduresIblock['ID'];

if ($doctorId > 0) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['procedures'])) {
        $procedureIds = array_map('intval', $_POST['procedures']);
        \CIBlockElement::SetPropertyValuesEx($doctorId, $doctorsIblockId, ['PROCEDURES' => $procedureIds]);
        LocalRedirect("/doctors/?DOCTOR_ID={$doctorId}");
    }

    $doctor = DoctorsTable::getList([
        'select' => ['DOCTOR_ID' => 'ELEMENT.ID', 'DOCTOR_NAME' => 'ELEMENT.NAME', 'CABINET', 'PROCEDURES'],
        'filter' => ['ELEMENT.ID' => $doctorId]
    ])->fetch();

    $allProcedures = ElementTable::getList([
        'filter' => ['IBLOCK_ID' => $proceduresIblockId],
        'select' => ['ID', 'NAME']
    ])->fetchAll();

    $proceduresMap = [];
    foreach ($allProcedures as $proc) {
        $proceduresMap[$proc['ID']] = $proc['NAME'];
    }

    $formProcedureIds = [];
    if ($doctor && !empty($doctor['PROCEDURES'])) {
        $formProcedureIds = $doctor['PROCEDURES'];
        $procNames = [];
        foreach ($doctor['PROCEDURES'] as $id) {
            if (isset($proceduresMap[$id])) {
                $procNames[] = $proceduresMap[$id];
            }
        }
        $doctor['PROCEDURES'] = $procNames;
    }
    ?>
    <div class="doctor-detail-card">
        <div class="doctor-detail-header">
            <div class="doctor-photo">
                <svg width="120" height="120" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="60" cy="60" r="60" fill="#ccc"/>
                    <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#666" font-size="14">Фото</text>
                </svg>
            </div>
            <div class="doctor-info" style="width: 100%;">
                <div style="display: flex; width: 100%;">
                    <div style="width: 50%;">
                        <h2><?= htmlspecialchars($doctor['DOCTOR_NAME']) ?></h2>
                        <div class="cabinet">Кабинет: № <?= htmlspecialchars($doctor['CABINET']) ?></div>
                        <?php if (!empty($doctor['PROCEDURES'])): ?>
                            <h4>Процедуры:</h4>
                            <ul class="procedures">
                                <?php foreach ($doctor['PROCEDURES'] as $procName): ?>
                                    <li><?= htmlspecialchars($procName) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                    <div style="width: 50%;">
                        <form method="post">
                            <label for="procedures">Добавить / изменить процедуры:</label><br>
                            <select name="procedures[]" id="procedures" multiple size="6">
                                <?php foreach ($proceduresMap as $id => $name): ?>
                                    <option value="<?= $id ?>" <?= in_array($id, $formProcedureIds) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select><br><br>
                            <button type="submit">Сохранить</button>
                        </form>
                    </div>
                </div>
                <a href="/doctors/" class="back-link">← Вернуться к списку врачей</a>
            </div>
        </div>
    </div>
    <?php
} else {
    $doctors = DoctorsTable::getList([
        'select' => ['DOCTOR_ID' => 'ELEMENT.ID', 'DOCTOR_NAME' => 'ELEMENT.NAME', 'CABINET', 'PROCEDURES']
    ])->fetchAll();

    $uniqueDoctors = [];
    foreach ($doctors as $doctor) {
        $key = $doctor['DOCTOR_NAME'] . '|' . $doctor['CABINET'];
        if (!isset($uniqueDoctors[$key]) || $doctor['DOCTOR_ID'] < $uniqueDoctors[$key]['DOCTOR_ID']) {
            $uniqueDoctors[$key] = $doctor;
        }
    }
    $doctors = array_values($uniqueDoctors);

    $allProcedureIds = [];
    foreach ($doctors as $doctor) {
        if (!empty($doctor['PROCEDURES'])) {
            $allProcedureIds = array_merge($allProcedureIds, $doctor['PROCEDURES']);
        }
    }
    $allProcedureIds = array_unique($allProcedureIds);

    $dbProcedures = ElementTable::getList([
        'filter' => ['IBLOCK_ID' => $proceduresIblockId, 'ID' => $allProcedureIds],
        'select' => ['ID', 'NAME']
    ])->fetchAll();

    $proceduresMap = [];
    foreach ($dbProcedures as $proc) {
        $proceduresMap[$proc['ID']] = $proc['NAME'];
    }

    foreach ($doctors as &$doctor) {
        if (!empty($doctor['PROCEDURES'])) {
            $procNames = [];
            foreach ($doctor['PROCEDURES'] as $id) {
                if (isset($proceduresMap[$id])) {
                    $procNames[] = $proceduresMap[$id];
                }
            }
            $doctor['PROCEDURES'] = $procNames;
        }
    }
    unset($doctor);
    ?>
    <div class="doctors-grid">
        <?php foreach ($doctors as $doctor): ?>
            <div class="doctor-card">
                <h3><?= htmlspecialchars($doctor['DOCTOR_NAME']) ?></h3>
                <div class="cabinet">Кабинет: № <?= htmlspecialchars($doctor['CABINET']) ?></div>
                <?php if (!empty($doctor['PROCEDURES'])): ?>
                    <ul class="procedures">
                        <?php foreach ($doctor['PROCEDURES'] as $procName): ?>
                            <li><?= htmlspecialchars($procName) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <a class="details-link" href="/doctors/?DOCTOR_ID=<?= $doctor['DOCTOR_ID'] ?>">Подробнее</a>
            </div>
        <?php endforeach; ?>
    </div>
<?php } ?>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/bitrix/footer.php"; ?>