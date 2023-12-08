<?php



namespace Solenoid\Tasker;



class Task
{
    private string $basedir;
    private string $id;



    # Returns [self]
    public function __construct (string $basedir, string $id)
    {
        // (Getting the value)
        $this->basedir = $basedir;
        $this->id      = $id;
    }

    # Returns [Task]
    public static function select (string $basedir, string $id)
    {
        // Returning the value
        return new Task( $basedir, $id );
    }



    # Returns [bool]
    public function exists ()
    {
        // Returning the value
        return is_dir( "$this->basedir/$this->id" );
    }



    # Returns [bool] | Throws [Exception]
    public function create ()
    {
        // (Getting the value)
        $folder_path = "$this->basedir/$this->id";

        if ( is_dir( $folder_path ) )
        {// (Directory found)
            // (Setting the value)
            $message = "Cannot create the task :: Task '$this->id' already exists !";

            // Throwing an exception
            throw new \Exception($message);

            // Returning the value
            return false;
        }



        if ( !mkdir( $folder_path ) )
        {// (Unable to make the directory)
            // (Setting the value)
            $message = "Unable to make the directory";

            // Throwing an exception
            throw new \Exception($message);

            // Returning the value
            return false;
        }



        if ( file_put_contents( "$folder_path/state", '1' ) === false )
        {// (Unable to write the content to the file)
            // (Setting the value)
            $message = "Unable to write the content to the file";

            // Throwing an exception
            throw new \Exception($message);

            // Returning the value
            return false;
        }



        // (Making the directories)
        mkdir( "$folder_path/data" );
        mkdir( "$folder_path/scripts" );



        // Returning the value
        return true;
    }



    # Returns [string]
    public function get_id ()
    {
        // Returning the value
        return $this->id;
    }



    # Returns [string]
    public function get_state ()
    {
        // Returning the value
        return file_get_contents( "$this->basedir/$this->id/state" ) === '1' ? 'ENABLED' : 'DISABLED';
    }

    # Returns [bool]
    public function set_state (bool $value)
    {
        if ( file_put_contents( "$this->basedir/$this->id/state", $value ? '1' : '0' ) === false )
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



    # Returns [array<string>]
    public function get_scripts ()
    {
        // Returning the value
        return array_values( array_filter( scandir( "$this->basedir/$this->id/scripts" ), function ($entry) { return !in_array( $entry, [ '.', '..' ] ); } ) );
    }

    # Returns [assoc]
    public function get_info ()
    {
        // Returning the value
        return
            [
                'id'      => $this->get_id(),

                'state'   => $this->get_state(),
                'scripts' => $this->get_scripts()
            ]
        ;
    }



    # Returns [bool]
    public function script_exists (string $id)
    {
        // Returning the value
        return is_dir( "$this->basedir/$this->id/scripts/$id" );
    }

    # Returns [bool] | Throws [Exception]
    public function add_script (string $id, string $bootstrap_file_path = '__DIR__ . \'/../../../../bootstrap.php\'')
    {
        // (Getting the value)
        $folder_path = "$this->basedir/$this->id/scripts/$id";

        if ( is_dir( $folder_path ) )
        {// (Directory found)
            // (Setting the value)
            $message = "Cannot add the script :: Script '$id' of the task '$this->id' already exists";

            // Throwing an exception
            throw new \Exception($message);

            // Returning the value
            return false;
        }



        // (Making the directories)
        mkdir( $folder_path );



        // (Getting the value)
        $script_file_content =
            <<<END
            <?php



            include_once( $bootstrap_file_path );



            use \Solenoid\Tasker\Script;
            use \Solenoid\Tasker\Config;



            Script::run
            (
                function ()
                {
                    if ( Script::\$args )
                    {// Value is not empty
                        // (Getting the value)
                        \$config = Config::read();

                        // Printing the value
                        echo "\\n\\nHello World !!!\\n\\n\\n";
                    }
                    else
                    {// Value is empty
                        // (Getting the value)
                        \$helper = <<<EOD

                        
                        USAGE :

                        <var-1> <var-2> ?<opt-var-3>



                        EOD
                        ;



                        // Printing the value
                        echo \$helper;
                    }
                }
            )
            ;



            ?>
            END
        ;



        // (Writing the content to the files)
        file_put_contents( "$folder_path/script.php", $script_file_content );
        file_put_contents( "$folder_path/config.json", '{}' );



        // Returning the value
        return true;
    }



    # Returns [array<string>]
    public static function list (string $basedir)
    {
        // (Getting the value)
        $list = array_values( array_filter( scandir( $basedir ), function ($entry) { return !in_array( $entry, [ '.', '..' ] ); } ) );
        $list = array_map
        (
            function ($id) use ($basedir)
            {
                // Returning the value
                return Task::select( $basedir, $id )->get_info();
            },
            $list
        )
        ;



        // Returning the value
        return $list;
    }



    # Returns [string]
    public function summarize ()
    {
        // Returning the value
        return $this->get_id() . ' -> ' . $this->get_state() . "\n[\n\t" . implode( "\n\t", array_map( function ($script_id) { return $script_id . ' -> `' . "$this->basedir/$this->id/scripts/$script_id/script.php" . '`'; }, $this->get_scripts() ) ) . "\n]";
    }



    # Returns [string]
    public function __toString ()
    {
        // Returning the value
        return $this->summarize();
    }
}



?>