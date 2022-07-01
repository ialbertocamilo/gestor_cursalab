<?php

namespace App\Models\Support;

use Config;
use DB;

class OTFConnection {
    /**
     * The name of the database we're connecting to on the fly.
     *
     * @var string $database
     */
    protected $database;
    /**
     * The on the fly database connection.
     *
     * @var \Illuminate\Database\Connection
     */
    protected $connection;
    /**
     * Create a new on the fly database connection.
     *
     * @param  array $settings
     * @return void
     */
    public function __construct($settings = null)
    {
        // Set the database
        $settings['database'] = $settings['name'];

        $database = $settings['name'];

        $this->database = $database;

        // Figure out the driver and get the default configuration for the driver

        $connection = $settings['driver'] . '_external';

        $default = Config::get("database.connections.$connection");

        // Loop through our default array and update settings if we have non-defaults
        foreach($default as $item => $value)
        {
            $default[$item] = isset($settings[$item]) ? $settings[$item] : $default[$item];
        }

        // Set the temporary configuration
        Config::set("database.connections.$connection", $default);

        // Create the connection
        $this->connection = DB::connection($connection);
    }
    /**
     * Get the on the fly connection.
     *
     * @return \Illuminate\Database\Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }
    /**
     * Get a table from the on the fly connection.
     *
     * @var    string $table
     * @return \Illuminate\Database\Query\Builder
     */
    public function getTable($table = null)
    {
        return $this->getConnection()->table($table);
    }
}
