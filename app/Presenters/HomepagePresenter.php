<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\components\ItemFormFactory;
use App\Model\CurrencyConversion;
use App\Model\Utils;
use Nette;
use Nette\Application\UI\Form;
use Nette\Utils\Html;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;
use Nette\Forms\Controls;
use Ublaboo\DataGrid\Localization\SimpleTranslator;

final class HomepagePresenter extends Nette\Application\UI\Presenter {

    private CurrencyConversion $currencyConversion;
    private Nette\Database\Explorer $database;
    private ItemFormFactory $itemFormFactory;

    public function __construct(CurrencyConversion $currencyConversion,
                                Nette\Database\Explorer $database,
                                ItemFormFactory $itemFormFactory) {
        $this->currencyConversion = $currencyConversion;
        $this->database = $database;
        $this->itemFormFactory = $itemFormFactory;
    }

    public function renderDefault() {
        $this->template->test = $this->currencyConversion->getConversionRate('EUR');
    }

    /**
     * Operating function for modal redraw signal
     * @param $modalId
     * @return void
     */
    public function handleModal($modalId) {
        $this->template->modal = $modalId;
    }

    /** Handler function to handle the successful submission of the form to create the item
     * @param Form $form
     * @param array $data
     * @return void
     */
    public function addItemFormSucceeded(Form $form, array $data) {

        $item = $this->database->table('ITEM')->insert([
            'name' => $data['name'],
            'price' => $data['price'],
            'publish_date' => $data['publish_date'],
            'category_id' => $data['category_id'] != 0 ? $data['category_id'] : null,
            'active' => $data['active'],
        ]);

        if(count($data['tags']) != 0) {
            $tags = Utils::getArrayForDynamicInsert($item->id, $data['tags'], "item_id", 'tag_id');

            $this->database->table('ITEM_TAG')
                ->insert($tags);
        }

        $this->flashMessage('Přidání položky proběhlo úspěšně', 'alert-success');
        $this->redrawControl('flashMessages');
        $this->getComponent('itemGrid')->redrawControl();
    }

    /**
     * Handler function for handling the successful submission of the form for editing the item
     * @param Form $form
     * @param array $data
     * @return void
     */
    public function editItemFormSucceeded(Form $form, array $data) {

        $item = $this->database->table('ITEM')->get($data['id']);
        $item->update([
            'name' => $data['name'],
            'price' => $data['price'],
            'publish_date' => $data['publish_date'],
            'category_id' => $data['category_id'] != 0 ? $data['category_id'] : null,
            'active' => $data['active'],
        ]);

        $item->related('ITEM_TAG')->delete();

        if(count($data['tags']) != 0) {
            $tags = Utils::getArrayForDynamicInsert($item->id, $data['tags'], "item_id", 'tag_id');

            $this->database->table('ITEM_TAG')
                ->insert($tags);
        }

        $this->flashMessage('Úprava položky proběhla úspěšně', 'alert-success');
        $this->redrawControl('flashMessages');
        $this->getComponent('itemGrid')->redrawControl();
    }

    /** Handler function for item addition signal
     * @return void
     */
    public function handleAddItem() {

        $this->template->modalType = 'add';
        $form = $this->getComponent('addItemForm');
        $this->template->formAction = 'edit';
        $this->handleModal('edit');
        // nebo si modal otevřeme sami:
        $this->redrawControl('modal');

    }

    /**
     * Operating function for the item editing signal
     * @param $id
     * @return void
     * @throws Nette\Application\BadRequestException
     */
    public function handleEditItem($id) {

        $itemId = intval($id);

        $item = $this->database->table('ITEM')->get($itemId);
        if(!$item) {
            $this->error();
        }

        $this->template->modalType = 'edit';

        $form = $this->getComponent('editItemForm');
        $form->setDefaults($item);
        $form->getComponent('publish_date')
            ->setDefaultValue($item->publish_date ? $item->publish_date->format('Y-m-d') : '');
        $form->getComponent('tags')
            ->setDefaultValue($item->related('ITEM_TAG', 'item_id')
                ->select('tag_id')->select('TAG.name AS tag_name')->fetchPairs('tag_name', 'tag_id'));

        $this->template->formAction = 'edit';
        // otevřeme modal přes signál v BasePresenteru
        $this->handleModal('edit');

        // nebo si modal otevřeme sami:
        $this->template->itemId = $itemId;
        $this->redrawControl('modal');
    }

    /**
     * Operating function for the delete item signal
     * @param $id
     * @return void
     */
    public function handleDelete($id) {
        bdump("delete " . $id);
        $this->database->table('ITEM_TAG')->where('item_id', $id)->delete();
        $this->database->table('ITEM')->get($id)->delete();
        $this->flashMessage('Odstranění položky proběhlo úspěšně', 'alert-success');
        $this->redrawControl('flashMessages');
        $this->getComponent('itemGrid')->redrawControl();
    }

    /**
     * Function for creating a form component for creating a new item
     * @return Form
     */
    public function createComponentAddItemForm():Form {

        $form = $this->itemFormFactory->create();

        $form->onSuccess[] = [$this, 'addItemFormSucceeded'];
        
        $form->onSuccess[] = function() {
            if(!$this->isAjax()) {
                $this->redirect('this');
            } else {
                $this->redrawControl('modal');
            }
        };
        return $form;
    }

    /**
     * Function for creating a form component for item editing
     * @return Form
     */
    public function createComponentEditItemForm():Form {

        $form = $this->itemFormFactory->create();

        $form->onSuccess[] = [$this, 'editItemFormSucceeded'];

        $form->onSuccess[] = function() {
            if(!$this->isAjax()) {
                $this->redirect('this');
            } else {
                $this->redrawControl('modal');
            }
        };
        return $form;
    }

    /**
     * Creating a Datagrid component for dynamic display of items
     * @param $name
     * @return DataGrid
     * @throws \Ublaboo\DataGrid\Exception\DataGridException
     */
    public function createComponentItemGrid($name) {

        $grid = new DataGrid($this, $name);
        $grid->setDataSource($this->database->table('ITEM')->select('*'));

        $grid->addColumnNumber('id', '#')->setSortable();
        $grid->addColumnText('name', 'Název produktu')->setSortable();
        $grid->addColumnNumber('priceCZK', 'Cena produktu [CZK]', 'price')->setSortable()->setRenderer(function ($item) {
            return number_format($item->price, 2, ',', ' ') . ' Kč';
        });

        $grid->addColumnNumber('priceEUR', 'Cena produktu [EUR]', 'price')->setSortable()->setRenderer(function($item) {
            return '€' . number_format(ceil($this->currencyConversion->convertCzkToEur($item->price) * 100)/100, 2, '.',' ');
        });

        $grid->addColumnText('category', 'Kategorie', 'category.name')->setRenderer(function($item) {
            if($item->category) {
                return Html::fromHtml('<span class="badge badge-success">' . $item->category->name . '</span>');
            }

            return '';
        });

        $grid->addColumnDateTime('publish_date', 'Datum publikace', 'publish_date')->setSortable();

        $grid->addColumnText('tags', 'Štítky', 'id')->setRenderer(function($item) {
            $tags = $item->related('ITEM_TAG');

            $result = '';

            foreach($tags as $tag) {
                $result .= '<span class="badge badge-primary">' . $tag->tag->name . '</span> ';
            }

            //return Html::el($result);
            return Html::fromHtml($result);
        });

        $grid->addColumnText('active', 'Aktivní', 'active')->setRenderer(function($item) {
            if($item->active == 1) {
                return Html::fromHtml('<i class="fas fa-check text-success"></i>');
            }

            return Html::fromHtml('<i class="fas fa-times text-danger"></i>');
        })->setSortable()->addCellAttributes(['class' => 'text-center']);

        $grid->addAction('edit', '', 'editItem!')
            ->setIcon('edit')
            ->setTitle('Upravit')
            ->setClass('btn btn-sm btn-primary ajax');

        $grid->addAction('delete', '', 'delete!')
            ->setIcon('trash')
            ->setTitle('Delete')
            ->setClass('btn btn-sm btn-danger ajax')
            ->setConfirmation(
                new StringConfirmation('Opravdu chcete odstranit položku "%s"?', 'name')
            );

        $grid->setTemplateFile(__DIR__ . '/templates/datagrid/template.latte');

        $translator = new SimpleTranslator([
            'ublaboo_datagrid.no_item_found_reset' => 'Žádné položky nenalezeny. Filtr můžete vynulovat',
            'ublaboo_datagrid.no_item_found' => 'Žádné položky nenalezeny.',
            'ublaboo_datagrid.here' => 'zde',
            'ublaboo_datagrid.items' => 'Položky',
            'ublaboo_datagrid.all' => 'všechny',
            'ublaboo_datagrid.from' => 'z',
            'ublaboo_datagrid.reset_filter' => 'Resetovat filtr',
            'ublaboo_datagrid.group_actions' => 'Hromadné akce',
            'ublaboo_datagrid.show_all_columns' => 'Zobrazit všechny sloupce',
            'ublaboo_datagrid.hide_column' => 'Skrýt sloupec',
            'ublaboo_datagrid.action' => 'Akce',
            'ublaboo_datagrid.previous' => 'Předchozí',
            'ublaboo_datagrid.next' => 'Další',
            'ublaboo_datagrid.choose' => 'Vyberte',
            'ublaboo_datagrid.execute' => 'Provést',

            'Name' => 'Jméno',
            'Birthday' => 'Narozky',
            'Age' => 'Věk',
        ]);

        $grid->setTranslator($translator);
        return $grid;
    }


}
