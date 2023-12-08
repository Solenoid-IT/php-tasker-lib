<?php



namespace Solenoid\Tasker;



use \Solenoid\Log\Logger;



class Tasker
{
    private static Tasker $instance;



    public static string  $basedir;

    public static Logger  $error_logger;
    public static Logger  $call_logger;



    # Returns [self]
    private function __construct (string $basedir, Logger $error_logger, Logger $call_logger)
    {
        // (Getting the values)
        self::$basedir      = $basedir;

        self::$error_logger = $error_logger;
        self::$call_logger  = $call_logger;
    }



    # Returns [self]
    public static function init (string $basedir, Logger $error_logger, Logger $call_logger)
    {
        if ( !isset( self::$instance ) )
        {// Value not found
            // (Creating a Tasker)
            self::$instance = new Tasker( $basedir, $error_logger, $call_logger );
        }



        // Returning the value
        return self::$instance;
    }



    # Returns [string|false] | Throws [Exception]
    public static function build_executable_path (string $task_id, string $script_id, array $script_args = [])
    {
        // (Getting the value)
        $task_basedir = self::$basedir . "/tasks/$task_id";

        if ( !is_dir( $task_basedir ) )
        {// (Task not found)
            // (Setting the value)
            $message = "Cannot run the process :: Task '$task_id' does not exist";

            // Throwing an exception
            throw new \Exception($message);

            // Returning the value
            return false;
        }



        // (Getting the value)
        $script_file_path = "$task_basedir/scripts/$script_id/script.php";

        if ( !file_exists( $script_file_path ) )
        {// (Script not found)
            // (Setting the value)
            $message = "Cannot run the process :: Script '$script_id' of the task '$task_id' does not exist";

            // Throwing an exception
            throw new \Exception($message);

            // Returning the value
            return false;
        }



        // (Getting the values)
        $script_file_path = escapeshellarg( $script_file_path );
        $script_args      = implode( ' ', array_map( function ($arg) { return escapeshellarg( $arg ); }, $script_args ) );



        // (Getting the value)
        $executable_path = "/usr/bin/php $script_file_path $script_args";



        // Returning the value
        return $executable_path;
    }



    # Returns [void] | Throws [Exception]
    public static function run (string $task_id, string $script_id, array $script_args = [])
    {
        // (Getting the value)
        $executable_path = self::build_executable_path( $task_id, $script_id, $script_args );

        if ( $executable_path === false )
        {// (Unable to build the executable path)
            // (Setting the value)
            $message = "Unable to build the executable path";

            // Throwing an exception
            throw new \Exception($message);

            // Returning the value
            return;
        }



        // (Executing the command)
        $result = shell_exec( $executable_path );



        // Returning the value
        return $result;
    }
}



?>