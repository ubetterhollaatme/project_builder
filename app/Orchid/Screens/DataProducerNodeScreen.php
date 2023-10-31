<?php

namespace App\Orchid\Screens;

use App\Models\DataProducerNode;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;

class DataProducerNodeScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Сборщик проекта';
    }

    /**
     * The description is displayed on the user's screen under the heading
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Зарегистрируйте ваши центры приёма данных';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [

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
                    ->title('Номер телефона'),

                Quill::make('desc')
                    ->title('Описание')
                    ->placeholder('Новый центр на Наб. реки Мойки')
                    ->help('Добавьте описание вашего центра'),

                Button::make('Зарегистрировать центр')
                    ->icon('note')
                    ->method('addDataProducerNode')
                    ->confirm(__('Вы уверены?'))
            ])
        ];
    }

    /**
     * redirect
     *
     * @param Request $request
     *
     * @return void
     */
    public function addDataProducerNode(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3',
            'desc' => 'required|min:5',
            'email' => 'required|email|unique:data_producer_nodes',
            'phone' => 'required|unique:data_producer_nodes',
        ]);

        $dpn = new DataProducerNode([
            'name' => $request->post('name'),
            'desc' => $request->post('desc'),
            'email' => $request->post('email'),
            'phone' => preg_replace('/\D/','', $request->post('phone')),
        ]);

        try {
            $dpn->save();
        } catch (\Throwable $e) {
            Alert::info($e->getMessage());
            return null;
        }
        /*
        Mail::raw($request->get('content'), function (Message $message) use ($request) {
            $message->from('ubetterhollaatme@yandex.ru');
            $message->to($email);
            $message->subject($request->get('subject'));
        });
        */
        Alert::info("Центр #{$dpn->getAttribute('id')} успешно зарегистрирован");
    }
}
