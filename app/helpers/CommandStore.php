<?php

/**
 * Data-access object for Command objects.
 */
class CommandStore {

    /** The MySQL database. */
    protected $pdo;

    /**
     * Creates the CommandStore.
     *
     * @param PDO $pdo  the MySQL database
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Retrieves a page of Command objects.
     *
     * @param array $args  an array of arguments:
     *   - start - the inclusive start index
     *   - count - the number of Commands to retrieve
     *   - q - the search term (optional)
     *   - orderBy - the ORDER BY clause
     * @return array  the Commands
     */
    public function findCommands($args) {
        $q = isset($args['q']) ? $args['q'] : '';
        $where = '';
        if (strlen($q) > 0) {
            $where = "WHERE name LIKE :q1 OR description LIKE :q2 OR url LIKE :q3";
        }
        $query = $this->pdo->prepare('SELECT * FROM yubnub.commands ' . $where . ' ORDER BY ' . $args['orderBy'] . ' LIMIT :start, :count');
        $query->bindValue(':start', $args['start'], PDO::PARAM_INT);
        $query->bindValue(':count', $args['count'], PDO::PARAM_INT);
        if (strlen($q) > 0) {
            $query->bindValue(':q1', '%' . $q . '%', PDO::PARAM_STR);
            $query->bindValue(':q2', '%' . $q . '%', PDO::PARAM_STR);
            $query->bindValue(':q3', '%' . $q . '%', PDO::PARAM_STR);
        }
        $query->execute();
        $commands = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $commands[] = $this->createCommand($row);
        }
        return $commands;
    }

    /**
     * Retrieves a page of featured Command objects.
     *
     * @param array $args  an array of arguments:
     *   - start - the inclusive start index
     *   - count - the number of Commands to retrieve
     *   - q - the search term (optional)
     *   - orderBy - the ORDER BY clause
     * @return array  the Commands
     */
    public function findGoldenEggs($args) {
        $q = isset($args['q']) ? $args['q'] : '';
        $where = 'WHERE golden_egg_date IS NOT NULL';
        if (strlen($q) > 0) {
            $where .= " AND (name LIKE :q1 OR description LIKE :q2 OR url LIKE :q3)";
        }
        $query = $this->pdo->prepare('SELECT * FROM yubnub.commands ' . $where . ' ORDER BY ' . $args['orderBy'] . ' LIMIT :start, :count');
        $query->bindValue(':start', $args['start'], PDO::PARAM_INT);
        $query->bindValue(':count', $args['count'], PDO::PARAM_INT);
        if (strlen($q) > 0) {
            $query->bindValue(':q1', '%' . $q . '%', PDO::PARAM_STR);
            $query->bindValue(':q2', '%' . $q . '%', PDO::PARAM_STR);
            $query->bindValue(':q3', '%' . $q . '%', PDO::PARAM_STR);
        }
        $query->execute();
        $commands = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $commands[] = $this->createCommand($row);
        }
        return $commands;
    }

    /**
     * Returns a Command by name
     *
     * @param string $name  the name of the command to retrieve
     * @return Command  the Command, or null if not found
     */
    public function findCommand($name) {
        $query = $this->pdo->prepare('SELECT * FROM yubnub.commands WHERE name = :name');
        $query->bindValue(':name', $name, PDO::PARAM_STR);
        $query->execute();
        if ($query->rowCount() > 0) {
            $row = $query->fetch(PDO::FETCH_ASSOC);
            return $this->createCommand($row);
        }
        return null;
    }

    /**
     * Creates a Command object based on the given arguments.
     *
     * @param array  key-value pairs from the database
     */
    protected function createCommand($args) {
        $command = new Command();
        $command->name = $args['name'];
        $command->url = $args['url'];
        $command->description = $args['description'];
        $command->uses = $args['uses'];
        $command->creationDate = $args['creation_date'];
        $command->lastUseDate = $args['last_use_date'];
        $command->goldenEggDate = $args['golden_egg_date'];
        return $command;
    }

}
