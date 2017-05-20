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

$reflection = new \ReflectionExtension("sdl");

$jid = $this->data('object')->jid('module');

$this->data('module.' . $jid . '.name', 'sdl');
$this->data('module.' . $jid . '.jid', $jid);
$this->data('module.' . $jid . '.version', phpversion("sdl"));
$this->data('module.' . $jid . '.function', $reflection->getFunctions());
// $this->data('module.' . $jid . '.function.count',  count($reflection->getFunctions()));
$this->data('module.' . $jid . '.class.count', count($reflection->getClasses()));
$this->data('module.' . $jid . '.constant.count', count($reflection->getConstants()));
$this->data('module.' . $jid . '.name', 'sdl');

// $this->output(get_extension_funcs('sdl'));

// $url = $this->data('dir.module.data') . 'claxon.wav';
//audio($url);

$url = $this->data('dir.public') . 'Image' . Application::DS . 'Priya.bmp';

function waitForInput()
{
    $event = null;
    while( SDL_WaitEvent( $event ) )
    {
        if( in_array( $event['type'], array( SDL_MOUSEBUTTONDOWN, SDL_KEYDOWN ) ) )
        {
            break;
        }
    }
}

if( SDL_Init( SDL_INIT_VIDEO ) != 0 )
{
    die( SDL_GetError() );
}
$screen = SDL_SetVideoMode( 1920, 1080, 8, SDL_HWSURFACE | SDL_DOUBLEBUF );
if( null === $screen )
{
    die(SDL_GetError());
}
$color = '#efff87;';
SDL_WM_SetCaption( $title = 'Priya', $title );
$boxColor = SDL_MapRGB( $screen['format'], 0xef, 0xff, 0x87 );
$bgColor = SDL_MapRGB( $screen['format'], 0, 0, 0 );
$x = 35.0;
$y = 35.0;
$speed = 100;
$rect = array(
    'w' => 25,
    'h' => 20,
);
// Load Image
$hello = SDL_LoadBMP($url);

$hello['w'] = 1920;
$hello['h'] = 375;
$hello['clip_rect']['w'] = 1920;
$hello['clip_rect']['h'] = 375;

// Display image
$currentTime = SDL_GetTicks();
while( true )
{
    if( SDL_PollEvent( $event ) )
    {
        if( in_array( $event['type'], array( SDL_MOUSEBUTTONDOWN, SDL_QUIT ) ) ) break;
    }
    $oldTime = $currentTime;
    $currentTime = SDL_GetTicks();
    $deltaTime = ( $currentTime - $oldTime ) / 500.0;
    // Checks what keys (if any) the user has pressed down.
    $keys = SDL_GetKeyState($numKeys);
    if( $keys[SDLK_LEFT] )
        $x -= $speed * $deltaTime;
        if( $keys[SDLK_RIGHT] )
            $x += $speed * $deltaTime;
            if( $keys[SDLK_DOWN] )
                $y += $speed * $deltaTime;
                if( $keys[SDLK_UP] )
                    $y -= $speed * $deltaTime;
                    if( $keys[SDLK_ESCAPE] )
                        break;
                        // Resets the background.
                        SDL_FillRect( $screen, null, $bgColor );
                        // Draws the yellow box on its new position.
                        $rect['x'] = $x;
                        $rect['y'] = $y;

                        $destination = array();
                        $destination['x'] = $x;
                        $destination['y'] = $y;
                        $destination['h'] = 100;
                        $destination['w'] = 200;
                        SDL_BlitSurface($hello, NULL, $screen, $destination);
//                         SDL_FillRect( $screen, $rect, $boxColor );
                        SDL_Flip( $screen );
}
SDL_Quit();
die;
/*
 *
$w = new SDL_Window( "Foo window", 100, 50, 350, 300, SDL_Window::SHOWN|SDL_Window::RESIZABLE);
$w->SetTitle("Some new title");
unset($w); // will destroy the window
*/
/*
if (sdl_getversion($version)) {
    printf('Powered by PHP %s, SDL extension %s, SDL2 library %s' . PHP_EOL, phpversion(), phpversion('sdl'), implode('.', $version));
} else {
    trigger_error('SDL version could not be retrieved', E_USER_NOTICE);
}
*/


/*
$this->data('module.' . $jid . '.cpu.count', SDL_GetCPUCount());
$this->data('module.' . $jid . '.cpu.cache.line.size', SDL_GetCPUCacheLineSize());
$this->data('module.' . $jid . '.cpu.has.RDTSC', SDL_HasRDTSC());
$this->data('module.' . $jid . '.cpu.has.AltiVec', SDL_HasAltiVec());
$this->data('module.' . $jid . '.cpu.has.MMX', SDL_HasMMX());
$this->data('module.' . $jid . '.cpu.has.3dNow', SDL_Has3DNow());
$this->data('module.' . $jid . '.cpu.has.HasSSE', SDL_HasSSE());
$this->data('module.' . $jid . '.cpu.has.HasSSE2', SDL_HasSSE2());
$this->data('module.' . $jid . '.cpu.has.HasSSE3', SDL_HasSSE3());
$this->data('module.' . $jid . '.cpu.has.HasSSE41', SDL_HasSSE41());
$this->data('module.' . $jid . '.cpu.has.HasSSE42', SDL_HasSSE42());
$this->data('module.' . $jid . '.cpu.memory.size', SDL_GetSystemRAM());
*/
$this->output($this->data('module'));

function audio($url=''){
    SDL_Init('SDL_AUDIODRIVER=pulse');
    SDL_Init( SDL_INIT_AUDIO );
    $desired = array(
        'freq' => 22050,
        'format' => AUDIO_S16LSB,
        'channels' => 2, // Stereo
        'samples' => 4096,
    );
    $obtained = null;
    if( -1 === SDL_OpenAudio( $desired, $obtained ) )
    {
        fprintf( STDERR, 'Could not open audio %s' . PHP_EOL, SDL_GetError() );
    }
    $wavSpec = $wavBuffer = $wavLength = null;

    if(file_exists($url)){
        $wav = SDL_LoadWAV($url, $wavSpec, $wavBuffer, $wavLength );
    }
    if( null === $wav )
    {
        fprintf( STDERR, 'Could not open the WAV file: %s' . PHP_EOL, SDL_GetError() );
    }
    SDL_PauseAudio( 0 );
    while( SDL_AUDIO_PLAYING === SDL_GetAudioStatus() )
    {
        SDL_Delay( 450 );
    }
    SDL_FreeWav( $wavBuffer );
    SDL_CloseAudio();
}