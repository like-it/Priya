<?php
use Priya\Application;

/**
 * @author 		Remco van der Velde
 * @since 		2016-11-07
 * @version		1.0
 * @changeLog
 * 	-	all
 * @note
 *  - In Smarty bash coloring isn't working.
 */

/*
extension_loaded('opengl') || dl('opengl.' . PHP_SHLIB_SUFFIX);

$reflection = new \ReflectionExtension("OpenGl functions");

$jid = $this->data('object')->jid('module');

$this->data('module.' . $jid . '.name', 'sdl');
$this->data('module.' . $jid . '.jid', $jid);
$this->data('module.' . $jid . '.version', phpversion("opengl"));
$this->data('module.' . $jid . '.function', $reflection->getFunctions());
// $this->data('module.' . $jid . '.function.count',  count($reflection->getFunctions()));
$this->data('module.' . $jid . '.class.count', count($reflection->getClasses()));
$this->data('module.' . $jid . '.constant.count', count($reflection->getConstants()));
$this->data('module.' . $jid . '.name', 'sdl');

// var_dump($this->data('module'));

// $this->output(get_extension_funcs('sdl'));

// $url = $this->data('dir.module.data') . 'claxon.wav';
//audio($url);

$url = $this->data('dir.public') . 'Image' . Application::DS . 'Priya.bmp';

glutInit($argc, $argv);

glutInitWindowSize(800, 600);
glutInitWindowPosition(300, 100);
glutCreateWindow('Basic PHP-OpenGL example');

echo glGetString(GL_VENDOR), PHP_EOL;
echo glGetString(GL_RENDERER), PHP_EOL;

glutDisplayFunc(function() {
    glClearColor(0, 0, .2, 1);
    glClear(GL_COLOR_BUFFER_BIT);
    glutSwapBuffers();
});
glutMainLoop();

*/

class MainFrame extends \wxFrame
{
    function onQuit()
    {
        $this->Destroy();
    }

    function onAbout()
    {
        $dlg = new wxMessageDialog(
                $this,
                "Welcome to wxPHP!!\nBased on wxWidgets 3.0.0\n\nThis is a minimal wxPHP sample!",
                "About box...",
                wxICON_INFORMATION
                );

        $dlg->ShowModal();
    }

    function __construct()
    {
        parent::__construct(null, null, "Minimal wxPHP App", wxDefaultPosition, new wxSize(350, 260));

        $mb = new wxMenuBar();

        $mn = new wxMenu();
        $mn->Append(2, "E&xit", "Quit this program");
        $mb->Append($mn, "&File");

        $mn = new wxMenu();
        $mn->AppendCheckItem(4, "&About...", "Show about dialog");
        $mb->Append($mn, "&Help");

        $this->SetMenuBar($mb);

        $scite = new wxStyledTextCtrl($this);

        $sbar = $this->CreateStatusBar(2);
        $sbar->SetStatusText("Welcome to wxPHP...");

        $this->Connect(2, wxEVT_COMMAND_MENU_SELECTED, array($this,"onQuit"));
        $this->Connect(4, wxEVT_COMMAND_MENU_SELECTED, array($this,"onAbout"));
    }
}

class MyApp extends wxApp
{
    function OnInit()
    {
        $this->mf = new mainFrame();
        $this->mf->Show();

        return 0;
    }

    function OnExit()
    {
        return 0;
    }
}

$app = new MyApp();
wxApp::SetInstance($app);
wxEntry();
