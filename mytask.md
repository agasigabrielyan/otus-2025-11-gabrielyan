# Практика: свой модуль Bitrix24 / 1C-Bitrix (по лекции OTUS + расширенный курс)

> **Источник лекции:** `Написание_своего_модуля-558971-f9c0f9.pdf` (OTUS, Александр Холин)  
> **Режим работы:** ты пишешь код → ассистент проверяет → ты дополняешь свою лекцию/конспект  
> **Проект:** `c:\OSPanel\domains\otusgabrielyan.loc`  
> **Эталон в проекте (для сравнения):** `local/modules/otus.taskmanager`

---

## Что мы создаём

**Модуль для Маркетплейса Bitrix** с учебной, но реальной функциональностью:

| Параметр | Значение |
|----------|----------|
| **ID модуля** | `otus.marketdemo` (партнёр `otus`, короткое имя `marketdemo`) |
| **Namespace** | `Otus\Marketdemo` |
| **Суть** | «Демо-витрина уведомлений»: модуль хранит записи в своей таблице, показывает их в админке и на публичной странице, шлёт напоминания агентом, реагирует на события ядра |
| **Зачем такая тема** | Покрывает всё из твоего списка: меню, настройки с табами, ORM, агенты, события, компоненты, контроллеры, установка/удаление — и выглядит как нормальный продукт для Marketplace |

### Бизнес-идея (простая, чтобы не отвлекаться от техники)

- Админ создаёт «демо-записи» (заголовок + текст + дата публикации).
- Модуль раз в N минут (агент) помечает просроченные записи.
- При сохранении пользователя (событие) можно логировать действие в отдельную таблицу (опционально, шаг 9).
- На сайте — компонент `otus:marketdemo.list` выводит активные записи.
- В админке — пункт меню «Демо Marketplace» со списком и формой.

---

## Карта папок модуля (целевая структура)

```
local/modules/otus.marketdemo/
├── install/
│   ├── index.php              # класс CModule, DoInstall / DoUninstall
│   ├── version.php
│   ├── step.php / unstep.php
│   ├── db/
│   │   └── install.sql        # или установка через ORM Table::createDbTable()
│   ├── components/otus/marketdemo.list/   # копируются при InstallFiles
│   └── admin/                 # копируются в bitrix/admin при установке
│       └── otus_marketdemo_list.php
├── admin/
│   ├── menu.php               # пункты меню админки
│   └── otus_marketdemo_list.php
├── lib/
│   ├── ORM/
│   │   └── DemoItemTable.php
│   ├── Controller/
│   │   └── Item.php           # Engine Controller для AJAX
│   ├── Agent/
│   │   └── ExpireAgent.php
│   └── Event/
│       └── UserHandler.php
├── lang/ru/...
├── .settings.php                # routes, controllers namespace
├── include.php                  # автозагрузка (если нужна вне lib)
├── default_options.php
└── options.php                  # страница настроек с табами
```

---

## Пошаговый план (чеклист)

Отмечай `[x]` по мере выполнения. **Не переходи к следующему шагу, пока предыдущий не проверен.**

### Шаг 0 — Подготовка
- [ ] Создать ветку git / бэкап (по желанию)
- [ ] Убедиться, что сайт открывается, есть доступ в `/bitrix/admin/`
- [ ] Прочитать вопросы для самопроверки из PDF (в конце `mytask.md`)

### Шаг 1 — Каркас модуля (лекция: слайды 10–14 PDF)
**Ты делаешь:**
- [ ] Папка `local/modules/otus.marketdemo/`
- [ ] `install/version.php` — версия `1.0.0`, дата
- [ ] `install/index.php` — класс `otus_marketdemo extends CModule`
- [ ] Свойства: `MODULE_ID`, `MODULE_NAME`, `MODULE_DESCRIPTION`, `PARTNER_NAME`, `PARTNER_URI`
- [ ] `lang/ru/install/index.php` — языковые фразы
- [ ] `DoInstall()` → `InstallDB()` → `ModuleManager::registerModule`
- [ ] `DoUninstall()` → `UnInstallDB()` → `ModuleManager::unRegisterModule`
- [ ] Заглушки: `InstallFiles`, `InstallEvents`, `UnInstallFiles`, `UnInstallEvents`

**Проверка:** модуль виден в «Настройки → Настройки продукта → Модули», устанавливается и удаляется без фатальных ошибок.

**Конспект для лекции:** что такое модуль, `/local/modules` vs `/bitrix/modules`, обязательные методы инсталлятора.

---

### Шаг 2 — Страница настроек модуля (`options.php`) + табы
**Ты делаешь:**
- [x] `default_options.php` — ключи: `agent_interval`, `default_status`, `enable_log`
- [x] `options.php` — права `access` только для админов
- [x] Сохранение через `Option::set($module_id, $name, $value)`
- [x] **Два таба:** «Основные» и «Агент» (см. шпаргалку ниже)

**Проверка:** Настройки → Настройки модулей → otus.marketdemo → табы переключаются, значения сохраняются после F5.

**Конспект:** мнемоника TAB (см. раздел «Шпаргалки»).

---

### Шаг 3 — Таблицы БД и ORM
**Ты делаешь:**
- [x] `lib/ORM/DemoItemTable.php` extends `DataManager`
- [x] Поля: ID, TITLE, BODY, STATUS, DATE_PUBLISH, DATE_CREATE
- [x] В `InstallDB()` — `DemoItemTable::getEntity()->createDbTable()` (или SQL в `install/db/`)
- [x] В `UnInstallDB()` — `Connection::dropTable()` (с вопросом «удалить данные?» на unstep)

**Проверка:** таблица `b_otus_marketdemo_demo_item` (имя уточним по `getTableName()`) есть в БД.

**Конспект:** namespace `Otus\Marketdemo\ORM`, связь ID модуля ↔ namespace.

---

### Шаг 4 — Админ-меню и страница списка
**Ты делаешь:**
- [x] `admin/menu.php` — пункт в разделе «Сервис» или свой раздел
- [x] `admin/otus_marketdemo_list.php` — список через ORM или `CAdminList`
- [x] В `InstallFiles()` — `CopyDirFiles` admin → `/bitrix/admin/`
- [x] В `UnInstallFiles()` — `DeleteDirFilesEx` / удаление конкретных файлов

**Проверка:** после установки в меню админки есть пункт, страница открывается.

---

### Шаг 5 — Компонент (копирование при установке)
**Ты делаешь:**
- [x] `install/components/otus/marketdemo.list/` — class.php, template, .description.php
- [x] `InstallFiles()`: копирование в `/local/components/otus/`
- [x] `UnInstallFiles()`: удаление `/local/components/otus/marketdemo.list/`
- [x] Тестовая страница `/marketdemo/` с вызовом компонента

**Проверка:** компонент в списке визуального редактора, выводит данные из ORM.

**Конспект:** `CopyDirFiles` / `DeleteDirFilesEx` из PDF (слайд 14–15).

---

### Шаг 6 — Агенты
**Ты делаешь:**
- [x] `lib/Agent/ExpireAgent.php` — метод `run();` return `__METHOD__ . '();';`
- [x] Регистрация в `InstallDB()` или отдельном методе: `CAgent::AddAgent(...)`
- [x] Удаление в `UnInstallDB()`: `CAgent::RemoveModuleAgents($module_id)`
- [x] Интервал из опции `agent_interval`

**Проверка:** в «Настройки → Инструменты → Агенты» есть агент модуля; после ручного запуска меняется STATUS записей.

**Конспект:** агент всегда возвращает строку вызова себя же.

---

### Шаг 7 — События модуля
**Ты делаешь:**
- [x] `install/index.php` → `InstallEvents()` / `UnInstallEvents()`
- [x] `EventManager::registerEventHandler('main', 'OnAfterUserUpdate', ...)`
- [x] `lib/Event/UserHandler.php` — обработчик
- [x] Тест: изменить пользователя → проверить лог/запись

**Проверка:** обработчик есть в `b_module_to_module` (или через админку событий).

---

### Шаг 8 — Контроллеры (Engine + AJAX)
**Ты делаешь:**
- [ ] `.settings.php` — секция `controllers` → `defaultNamespace` => `Otus\Marketdemo\Controller`
- [ ] `lib/Controller/Item.php` — actions: `listAction`, `deleteAction`
- [ ] Вызов: `/bitrix/services/main/ajax.php?action=otus:marketdemo.api.item.list` (уточнить mode в .settings)

**Проверка:** POST из консоли браузера / Postman возвращает JSON.

**Конспект:** цепочка module_id → .settings.php → namespace → класс Controller → action.

---

### Шаг 9 — Подготовка к Marketplace (обзорно)
**Ты делаешь:**
- [ ] `README.md` модуля, скриншоты
- [ ] `install/version.php` — корректные VERSION / VERSION_DATE
- [ ] Проверка: установка на «чистом» модуле, deinstall с удалением таблиц и файлов
- [ ] (Опционально) `updater/` для версий 1.0.1+

**Проверка:** чеклист требований marketplace на dev.1c-bitrix.ru

---

## Шпаргалки (чтобы запомнить)

### Namespace и автозагрузка (lib/)

| ID модуля | Namespace | Пример класса | Файл |
|-----------|-----------|---------------|------|
| `otus.marketdemo` | `Otus\Marketdemo` | `Otus\Marketdemo\ORM\DemoItemTable` | `lib/ORM/DemoItemTable.php` |

**Правило:** `vendor.shortname` → `Vendor\Shortname` (точка → обратный слэш, каждая часть с большой буквы).

Подключение: `Bitrix\Main\Loader::includeModule('otus.marketdemo');`  
После этого классы из `/lib` подхватываются автоматически (PSR-4).

---

### Страница настроек и ТАБЫ — мнемоника **TAB**

1. **T**op — вверху `options.php`: проверка прав ` $RIGHT >= 'W' `, `IncludeModuleLangFile`, заголовок.
2. **A**rray tabs — массив `$aTabs = [ ['DIV'=>'edit1', 'TAB'=>..., 'TITLE'=>...], ... ];`
3. **B**egin — `$tabControl = new CAdminTabControl('tabControl', $aTabs);` → `$tabControl->Begin();`
4. В цикле по табам: `BeginNextTab()` → поля → `EndTab()`
5. **End** — `$tabControl->Buttons([...]);` → `$tabControl->End();`

Сохранение: если `$_REQUEST['Update']` и `check_bitrix_sessid()` → `COption::SetOptionString` или `Bitrix\Main\Config\Option::set`.

**Запомнить:** один `$tabControl` на все табы; `DIV` — уникальный id вкладки (латиница).

---

### Инсталлятор — порядок вызовов

```
DoInstall()
  → InstallDB()      // registerModule (если ещё нет) + includeModule + таблицы
  → InstallEvents()
  → InstallFiles()   // admin + components
  → (step.php)

DoUninstall()
  → UnInstallEvents()
  → UnInstallFiles()
  → UnInstallDB()    // includeModule + dropTable (если не savedata)
  → unRegisterModule (в unstep.php)
  → (unstep.php)
```

---

### Копирование / удаление файлов

```php
// Установка компонентов
CopyDirFiles(
    __DIR__ . '/components/otus',
    $_SERVER['DOCUMENT_ROOT'] . '/local/components/otus',
    true, true
);

// Удаление
DeleteDirFilesEx('/local/components/otus/marketdemo.list');
```

Админ-файлы: из `install/admin/` → `$_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'`.

---

### Агент — шаблон

```php
public static function run(): string
{
    // логика
    return '\\Otus\\Marketdemo\\Agent\\ExpireAgent::run();';
}
```

Регистрация: `CAgent::AddAgent('\\Otus\\Marketdemo\\Agent\\ExpireAgent::run();', 'otus.marketdemo', 'N', 60);`

---

### Контроллер — минимум в `.settings.php`

```php
return [
    'controllers' => [
        'value' => [
            'defaultNamespace' => '\\Otus\\Marketdemo\\Controller',
        ],
        'readonly' => true,
    ],
];
```

Класс: `extends \Bitrix\Main\Engine\Controller`, методы `xxxAction()`.

---

## Вопросы из PDF (ответы для самопроверки)

1. **Преимущество модульного подхода?** — изоляция кода, повторное использование, командная разработка, проще сопровождать и переносить между проектами.
2. **Может ли модуль обходиться без БД?** — да (только файлы, опции, агенты без своих таблиц).
3. **Где кастомные модули?** — `/local/modules/`.
4. **Перенос между сайтами?** — да, копируется папка модуля + установка через админку; для Marketplace — через маркет.

---

## Текущий статус

> **СТОП (2026-06-04):** шаг 7 готов. **Продолжать с шага 8** — Engine-контроллеры (AJAX).

| Шаг | Статус | Дата | Комментарий |
|-----|--------|------|-------------|
| 0 | ⬜ по желанию | | бэкап, доступ в админку |
| 1 | ✅ готово | 2026-06-03 | install, step/unstep |
| 2 | ✅ готово | 2026-06-04 | options.php + табы |
| 3 | ✅ готово | 2026-06-04 | ORM DemoItemTable |
| 4 | ✅ готово | 2026-06-04 | admin menu + list (простой вывод) |
| 5 | ✅ готово | 2026-06-04 | компонент + `/marketdemo/` |
| 6 | ✅ готово | 2026-06-04 | ExpireAgent |
| 7 | ✅ готово | 2026-06-04 | `UserHandler`, OnAfterUserUpdate, enable_log |
| 8–9 | ⬜ | | контроллеры, Marketplace |

### Продолжить с (шаг 8)

1. `.settings.php` — `controllers.defaultNamespace`
2. `lib/Controller/Item.php` — `listAction`, `deleteAction`
3. Проверка: AJAX из браузера / Postman

**В чат:** «Шаг 8» или «Напиши код шага 8».

### Заметки по текущему коду

- Админ-список упрощён (без lang), `menu.php` — тексты в файле
- `InstallDB`: registerModule + includeModule (без registerAutoLoadClasses)
- Отладка агента: PhpStorm Listen + ручной запуск в «Агенты»

---
| … | | | |

---

## Как работаем в чате

1. Ты пишешь: **«Шаг N, готово»** + путь к файлам или вставка кода.
2. Ассистент: проверяет структуру, типичные ошибки Bitrix, даёт правки.
3. Ты обновляешь свой конспект/лекцию и отмечаешь чекбокс в этом файле.
4. Переходим к следующему шагу.

**Стартовая команда для чата:** `Шаг 1 — создаю каркас otus.marketdemo`

---

## Ссылки

- [Урок: структура модуля](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=2823)
- [Создание модуля](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&CHAPTER_ID=03650)
- Локальный эталон: `local/modules/otus.taskmanager`
