<?php

namespace App\Orchid\Screens;

use App\Helpers\DockerComposeBuilder;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Alert;

/**
 *
 */
class BuilderScreen extends Screen
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
        return 'BuilderScreen';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Build project')
                ->modal('Builder form')
                ->icon('wrench')
                ->method('buildProject')
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
            Layout::modal('Builder form', [
                Layout::rows([
                    Input::make('name')
                        ->title('Node name')
                        ->placeholder('My node #1')
                        ->help('Enter your node name'),

                    Input::make('email')
                        ->type('email')
                        ->title('Associated E-mail')
                        ->placeholder('E-mail')
                        ->help('Enter E-mail for sending authorization data
                            of the administrative panel of new node'),

                    Input::make('phone')
                        ->mask('+7 (999) 999-9999')
                        ->title('Associated Phone Number')
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
     *
     * @return void
     */
    public function buildProject(Request $request): void
    {
//        $request->validate([
//            'name' => 'required|min:3',
//            'desc' => 'required|min:5',
//            'email' => 'required|unique:data_producer_nodes|email',
//            'phone' => 'required',
//        ]);

        $builder = new DockerComposeBuilder(
            [
                'version' => '3.7',
            ],
            yaml_parse_file('/var/www/html/docker/node/docker-compose.yml'));

        $builder->build();
        /*
        Mail::raw($request->get('content'), function (Message $message) use ($request) {
            $message->from('ubetterhollaatme@yandex.ru');
            $message->to($email);
            $message->subject($request->get('subject'));
        });
        */
        Alert::info("Project builded, you can move in /project/ folder
            and enter the command 'docker-compose up' to start the project");
    }
}
