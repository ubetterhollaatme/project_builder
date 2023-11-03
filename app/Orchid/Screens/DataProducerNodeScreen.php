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
        return 'Nodes';
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
            ModalToggle::make('Create node')
                ->modal('Node creating form')
                ->icon('plus')
                ->method('addDataProducerNode')
                ->confirm(__('Are you sure?')),

            Button::make('Generate nodes')
                ->icon('grid')
                ->method('generateNodes')
                ->confirm(__('Are you sure?')),

            Button::make('Clear nodes')
                ->icon('trash')
                ->method('clearNodes')
                ->confirm(__('Are you sure?')),
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
            Layout::modal('Node creating form', [
                Layout::rows([
                    Input::make('name')
                        ->title('Node name')
                        ->required()
                        ->placeholder('My node #1')
                        ->help('Enter your node name'),

                    Input::make('email')
                        ->type('email')
                        ->title('Associated E-mail')
                        ->required()
                        ->placeholder('E-mail')
                        ->help('Enter E-mail for sending authorization data
                            of the administrative panel of new node'),

                    Input::make('phone')
                        ->mask('+7 (999) 999-9999')
                        ->title('Associated Phone Number')
                        ->required()
                        ->placeholder('+7 (999) 999-9999')
                        ->help('Enter phone number of the node'),

                    TextArea::make('desc')
                        ->title('Node Description')
                        ->placeholder('My new super-mega-node')
                        ->help('Enter description of your node'),
                ])
            ])
                ->applyButton('Add')
                ->closeButton('Close')
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
    public function generateNodes(): void
    {
        DataProducerNode::factory()
            ->count(20)
            ->make()
            ->each(fn ($node) => $node->save());

        Alert::info("Nodes created");
    }

    /**
     * @return void
     */
    public function clearNodes(): void
    {
        DataProducerNode::truncate();

        Alert::info("Nodes cleared");
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
        Alert::info("Project builded, you can move in /project/ folder
            and enter the command 'docker-compose up' to start project");
    }
}
