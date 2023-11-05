<?php

namespace App\Orchid\Screens;

use App\Helpers\DockerComposeBuilder;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
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
                    Input::make('nodes_per_server')
                        ->title('Nodes Per Server')
                        ->placeholder('8')
                        ->help('Enter how much nodes do you want to place on each server'),
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
        $request->validate([
            'nodes_per_server' => 'required|min:1|integer',
        ]);

        $builder = new DockerComposeBuilder(
            [
                'version' => '3.7',
                'nodes_per_server' => $request->input('nodes_per_server'),
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
