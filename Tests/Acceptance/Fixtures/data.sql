-- Pages
INSERT INTO `pages` (`uid`, `pid`, `title`, `slug`, `doktype`, `is_siteroot`, `sorting`, `hidden`)
VALUES
  (2, 1, 'Simple Repeatable Form', '/simple-form', 1, 0, 256, 0),
  (3, 1, 'Nested Multi-Step Form', '/nested-form', 1, 0, 512, 0)
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`), `slug`=VALUES(`slug`);

-- Remove default content from root page (v14 setup creates a welcome element)
DELETE FROM `tt_content` WHERE `pid` = 1;

-- Content elements: Form plugins (use high UIDs to avoid conflicts)
INSERT INTO `tt_content` (`uid`, `pid`, `CType`, `header`, `sorting`, `colPos`, `pi_flexform`)
VALUES
  (100, 2, 'form_formframework', 'Simple Repeatable Form', 256, 0, '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>\n<T3FlexForms>\n    <data>\n        <sheet index="sDEF">\n            <language index="lDEF">\n                <field index="settings.persistenceIdentifier">\n                    <value index="vDEF">1:/form_definitions/repeatable-simple.form.yaml</value>\n                </field>\n            </language>\n        </sheet>\n    </data>\n</T3FlexForms>'),
  (101, 3, 'form_formframework', 'Nested Multi-Step Form', 256, 0, '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>\n<T3FlexForms>\n    <data>\n        <sheet index="sDEF">\n            <language index="lDEF">\n                <field index="settings.persistenceIdentifier">\n                    <value index="vDEF">1:/form_definitions/repeatable-nested.form.yaml</value>\n                </field>\n            </language>\n        </sheet>\n    </data>\n</T3FlexForms>')
ON DUPLICATE KEY UPDATE `pid`=VALUES(`pid`), `CType`=VALUES(`CType`), `header`=VALUES(`header`), `pi_flexform`=VALUES(`pi_flexform`);

-- Update root page title
UPDATE `pages` SET `title` = 'Repeatable Form Elements Test' WHERE `uid` = 1;
