<?php

namespace App\Model\components;

use Nette;
use Nette\Application\UI\Form;

class ItemFormFactory {

    private Nette\Database\Explorer $database;

    public function __construct(Nette\Database\Explorer $database) {
        $this->database = $database;
    }

    /**
     * Factory function to build the required form
     * @return Form
     */
    public function create():Form {

        $form = new Form();
        $form->getElementPrototype()->class('ajax');
        $form->addHidden('id');

        $form->addText('name', 'Název produktu')
            ->setRequired('Název musí být vyplněn.');

        $form->addText('price', 'Cena produktu (CZK)')
            ->addRule(Nette\Forms\Form::FLOAT, 'Hodnota musí být číslo.')
            ->setRequired('Cena musí být vyplněna.');

        $form->addText('publish_date', 'Datum publikace')
            ->setHtmlType('date')
            ->addRule($form::PATTERN, 'Nesprávný formát', '^\d{4}\-(0?[1-9]|1[012])\-(0?[1-9]|[12][0-9]|3[01])$')
            ->setRequired('Datum musí být vyplněn.');

        $form->addSelect('category_id', 'Kategorie produktu',
            array_merge([0 => ''], $this->database->table('CATEGORY')->fetchPairs('id', 'name')));
        $form->addMultiSelect('tags', 'Štítky produktu',
                $this->database->table('TAG')->fetchPairs('id', 'name'))
            ->setHtmlAttribute('class', 'multi-select');

        $form->addCheckbox('active', 'Aktivní');

        $form->addSubmit('submit');

        $this->makeBootstrap4($form);

        return $form;

    }

    /**
     * Function to apply bootstrap styles to a form
     * @param Form $form form instance
     * @return void
     */
    function makeBootstrap4(Form $form): void {
        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = null;
        $renderer->wrappers['pair']['container'] = 'div class="form-group row"';
        $renderer->wrappers['pair']['.error'] = 'has-danger';
        $renderer->wrappers['control']['container'] = 'div class=col-sm-12';
        $renderer->wrappers['label']['container'] = 'div class="col-sm-12"';
        $renderer->wrappers['control']['description'] = 'span class=form-text';
        $renderer->wrappers['control']['errorcontainer'] = 'span class=form-control-feedback';
        $renderer->wrappers['control']['.error'] = 'is-invalid';

        foreach ($form->getControls() as $control) {
            $type = $control->getOption('type');
            if ($type === 'button') {
                $control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-secondary');
                $usedPrimary = true;

            } elseif (in_array($type, ['text', 'textarea', 'select'], true)) {
                $control->getControlPrototype()->addClass('form-control');

            } elseif ($type === 'file') {
                $control->getControlPrototype()->addClass('form-control-file');

            } elseif (in_array($type, ['checkbox', 'radio'], true)) {
                if ($control instanceof Nette\Forms\Controls\Checkbox) {
                    $control->getLabelPrototype()->addClass('form-check-label');
                } else {
                    $control->getItemLabelPrototype()->addClass('form-check-label');
                }
                $control->getControlPrototype()->addClass('form-check-input');
                $control->getSeparatorPrototype()->setName('div')->addClass('form-check');
            }
        }
    }

}