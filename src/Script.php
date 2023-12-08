<?php



namespace Solenoid\Tasker;



use \Solenoid\Tasker\Tasker;
use \Solenoid\System\CallStack;
use \Solenoid\System\File;
use \Solenoid\Perf\Analyzer;



class Script
{
    private static Script $instance;



    public static array  $folder_paths;
    public static array  $args;

    public static string $file_path;



    # Returns [self]
    private function __construct ()
    {
        // (Setting the value)
        $folder_paths = [];

        // (Getting the values)
        $folder_paths['script'] = dirname( CallStack::fetch_origin()['file'] );
        $folder_paths['task']   = realpath( $folder_paths['script'] . '/../..' );
        $folder_paths['data']   = $folder_paths['task'] . '/data';



        // (Getting the value)
        self::$folder_paths = $folder_paths;



        // (Accessing to the variable)
        global $argv;

        // (Getting the values)
        self::$args      = array_splice( $argv, 1 );
        self::$file_path = preg_replace( '/^\./', $folder_paths['task'], $_SERVER['SCRIPT_NAME'] );
    }



    # Returns [bool|null] | Throws [Exception]
    public static function is_enabled ()
    {
        // (Getting the value)
        $file_content = File::select( self::$folder_paths['task'] . '/state' )->read();

        if ( $file_content === false )
        {// (Unable to get the content of the state file)
            // (Setting the value)
            $message = "Unable to get the content of the state file";

            // Throwing an exception
            throw new \Exception($message);

            // Returning the value
            return null;
        }



        // Returning the value
        return $file_content === '1';
    }



    # Returns [string]
    public static function get_user ()
    {
        // Returning the value
        return trim( shell_exec('whoami') );
    }



    # Returns [void] | Throws [Exception]
    public static function run (callable $function)
    {
        if ( isset( self::$instance ) ) return;



        // (Creating a Script)
        self::$instance = new Script();



        if ( !self::is_enabled() )
        {// (The script is not enabled)
            // Printing the value
            echo "\n\nTasker :: Script is DISABLED -> Execution has been BLOCKED\n\n\n";

            // Closing the process
            exit;
        }



        try
        {
            // (Creating an Analyzer)
            $performance_analyzer = Analyzer::create();



            // (Opening the analyzer)
            $performance_analyzer->open();



            // (Executing the function)
            $function();



            // (Closing the analyzer)
            $performance_analyzer->close();



            // (Pushing the message)
            Tasker::$call_logger->push( self::$file_path . ' -> ' . self::get_user() . ' -> ' . $performance_analyzer );
        }
        catch (\Exception $e)
        {
            // (Getting the value)
            $message = str_replace( "\n", ' >> ', (string) $e );

            // (Pushing the message)
            Tasker::$error_logger->push( $message );

            // Throwing an exception
            throw $e;

            // Returning the value
            return;
        }
    }
}



?>