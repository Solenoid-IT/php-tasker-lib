<?php



namespace Solenoid\Tasker;



use \Solenoid\System\File;



class Config
{
    # Returns [assoc]
    public static function read ()
    {
        // (Getting the value)
        $content = File::select( Script::$folder_paths['script'] . '/config.json' )->read();

        if ( $content === false )
        {// (Unable to read the content from the file)
            // (Setting the value)
            $message = "Unable to read the content from the file";

            // Throwing an exception
            throw new \Exception($message);

            // Returning the value
            return false;
        }



        // Returning the value
        return json_decode( $content, true );
    }

    # Returns [bool] | Throws [Exception]
    public static function write (array $data)
    {
        if ( File::select( Script::$folder_paths['script'] . '/config.json' )->write( json_encode( $data, JSON_PRETTY_PRINT ) ) === false )
        {// (Unable to write the content to the file)
            // (Setting the value)
            $message = "Unable to write the content to the file";

            // Throwing an exception
            throw new \Exception($message);

            // Returning the value
            return false;
        }



        // Returning the value
        return true;
    }
}



?>