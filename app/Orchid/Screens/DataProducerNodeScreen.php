<?php

namespace App\Orchid\Screens;

use App\Http\Controllers\DataProducerNodeController;
use App\Models\DataProducerNode;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;

/**
 *
 */
class DataProducerNodeScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'data_producer_nodes' => DataProducerNode::all()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Центры';
    }

    /**
     * The description is displayed on the user's screen under the heading
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return '';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Добавить центр')
                ->modal('Добавление центра')
                ->icon('full-screen')
                ->method('addDataProducerNode')
                ->confirm(__('Вы уверены?')),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('data_producer_nodes', [
                TD::make('id')->sort(),
                TD::make('name'),
                TD::make('email'),
                TD::make('phone'),
                TD::make('desc'),
            ]),
            Layout::modal('Добавление центра', [
                Layout::rows([
                    Input::make('name')
                        ->title('Название центра')
                        ->required()
                        ->placeholder('Кащенко - Троицкий пр. д. 1')
                        ->help('Введите название'),

                    Input::make('email')
                        ->type('email')
                        ->title('Получатель уведомления')
                        ->required()
                        ->placeholder('E-mail')
                        ->help('Введите адрес эл. почты для отправки авторизационных
                                    данных административной панели нового центра'),

                    Input::make('phone')
                        ->mask('+7 (999) 999-9999')
                        ->title('Номер телефона')
                        ->required()
                        ->placeholder('+7 (999) 999-99-99')
                        ->help('Введите контактный номер центра'),

                    TextArea::make('desc')
                        ->title('Описание')
                        ->placeholder('Новый центр на Наб. реки Мойки')
                        ->help('Добавьте описание вашего центра'),
                ])
            ])
                ->applyButton('Добавить')
                ->closeButton('Закрыть')
        ];
    }

    /**
     * @param Request $request
     * @param DataProducerNodeController $controller
     * @return void
     */
    public function addDataProducerNode(Request $request, DataProducerNodeController $controller)
    {
        try {
            $dpn = $controller->create($request);
        } catch (\Throwable $e) {
            Alert::info($e->getMessage());
            return null;
        }

        Alert::info("Центр #{$dpn->getAttribute('id')} успешно зарегистрирован");
    }

    /**
     * @return void
     */
    public function buildProject()
    {
        /*
        Mail::raw($request->get('content'), function (Message $message) use ($request) {
            $message->from('ubetterhollaatme@yandex.ru');
            $message->to($email);
            $message->subject($request->get('subject'));
        });
        */
        Alert::info("Платформа запущена");
    }
}
