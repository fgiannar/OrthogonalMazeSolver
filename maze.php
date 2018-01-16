<?php

/**
* Point
*/
class Point
{
    public $x;
    public $y;
    public $p;

    public function __construct($x, $y, $p)
    {
        $this->x = $x;
        $this->y = $y;
        $this->p = $p;
    }
}

class Maze
{
    /**
     * The maze image resource identifier.
     *
     * @var resource
     */
    public $image;
    /**
     * The image width.
     *
     * @var int
     */
    private $width;
    /**
     * The image height.
     *
     * @var int
     */
    private $height;
    /**
     * The left top boundary.
     *
     * @var Point
     */
    private $l;
    /**
     * The right bottom boundary.
     *
     * @var Point
     */
    private $r;
    /**
     * The solution start point.
     *
     * @var Point
     */
    private $s;
    /**
     * The solution end point.
     *
     * @var Point
     */
    private $e;
    /**
     * The queue holding possible solution paths.
     *
     * @var array
     */
    private $q;
    /**
     * An array holding visited coordinates.
     *
     * @var array
     */
    private $visited;

    public function __construct($img)
    {

        $this->image = imagecreatefrompng($img);
        list($this->width, $this->height) = getimagesize($img);
    }

    /**
     * Solves the maze using BFS (breadth-first search) algorithm in order to find
     * the shortest path between the maze's start and end points.
     *
     */
    public function solve()
    {
        // Initialize the maze.
        $this->initVisited()
            ->setBoundingBox()
            ->setStartEndPoints();

        // Populate the queue with the start node.
        $this->q = [$this->s];
        $target = null;
        // While queue is not empty keep exploring neighbours.
        while (sizeof($this->q)) {
            // Remove the first node from the queue (FIFO)
            $node = array_shift($this->q);
            // Check if the current node position is the end point and break the loop.
            if ($node->x == $this->e->x && $node->y == $this->e->y) {
                $target = $node;
                break;
            }

            // Check adjacent nodes.
            $this->checkNorthNode($node)
                ->checkSouthNode($node)
                ->checkEastNode($node)
                ->checkWestNode($node);
        }
        // Color the image with the solution.
        $red = imagecolorallocate($this->image, 255, 0, 0);
        while ($target) {
            imagesetpixel($this->image, $target->x, $target->y, $red);
            $target = $target->p;
        }
    }

    /**
     * Checks if pixel given coordinates are between bounds.
     *
     * @param int x
     * @param int y
     * @return boolean
     */
    private function inBounds($x, $y)
    {
        if ($x >= $this->l->x && $x <= $this->r->x) {
            if ($y >= $this->l->y && $y <= $this->r->y) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if pixel given coordinates are between bounds, not wall and not visited
     * and if so, adds a node to the queue and marks given coordinates
     * as visited.
     *
     * @param int x
     * @param int y
     * @param Point node
     * @return self
     */
    private function checkNode($x, $y, $node)
    {
        if ($this->inBounds($x, $y) && !$this->isWall($x, $y) && $this->visited[$x][$y] == false) {
            $this->q[] = new Point($x, $y, $node);
            $this->visited[$x][$y] = true;
        }

        return $this;
    }

    /**
     * Checks if the north neighbor of a given node should be added to the queue.
     *
     * @param Point node
     * @return self
     */
    private function checkNorthNode($node)
    {
        $x = $node->x;
        $y = $node->y - 1;

        return $this->checkNode($x, $y, $node);
    }

    /**
     * Checks if the south neighbor of a given node should be added to the queue.
     *
     * @param Point node
     * @return self
     */
    private function checkSouthNode($node)
    {
        $x = $node->x;
        $y = $node->y + 1;

        return $this->checkNode($x, $y, $node);
    }

    /**
     * Checks if the east neighbor of a given node should be added to the queue.
     *
     * @param Point node
     * @return self
     */
    private function checkEastNode($node)
    {
        $x = $node->x + 1;
        $y = $node->y;

        return $this->checkNode($x, $y, $node);
    }

    /**
     * Checks if the west neighbor of a given node should be added to the queue.
     *
     * @param Point node
     * @return self
     */
    private function checkWestNode($node)
    {
        $x = $node->x - 1;
        $y = $node->y;

        return $this->checkNode($x, $y, $node);
    }

    /**
     * Checks if a given image pixel is wall (aka is colored black)
     *
     * @param int x
     * @param int y
     * @return bool
     */
    private function isWall($x, $y)
    {
        return imagecolorat($this->image, $x, $y) == 0;
    }


    /**
     * Set the edges.
     *
     * @return self
     */
    private function setBoundingBox()
    {
        $this->l = new Point(0, 0, null);
        $this->r = new Point(0, 0, null);

        for ($i=0; $i < $this->width; $i++) {
            for ($j=0; $j < $this->height; $j++) {
                if ($this->isWall($i, $j)) {
                    if ($this->l->x == 0 && $this->l->y == 0 && !$this->l->p) {
                        $this->l = new Point($i, $j, null);
                    }
                    $this->r = new Point($i, $j, null);
                }
            }
        }

        return $this;
    }

    /**
     * Set the points to start and end with.
     *
     * @return self
     */
    private function setStartEndPoints()
    {
        $this->s = new Point(0, 0, null);
        $this->e = new Point(0, 0, null);

        for ($i=$this->l->x; $i < $this->r->x + 1; $i++) {
            if (!$this->isWall($i, $this->l->y)) {
                $this->e = new Point($i, $this->l->y, null);
                if ($this->s->x == 0 && $this->s->y == 0 && !$this->s->p) {
                    $this->s = new Point($i, $this->l->y, null);
                }
            }

            if (!$this->isWall($i, $this->r->y)) {
                $this->e = new Point($i, $this->r->y, null);
                if ($this->s->x == 0 && $this->s->y == 0 && !$this->s->p) {
                    $this->s = new Point($i, $this->r->y, null);
                }
            }
        }

        return $this;
    }

    /**
     * Initialize visited nodes array with false values.
     *
     * @return self
     */
    private function initVisited()
    {
        $this->visited = [];
        for ($i=0; $i < $this->width; $i++) {
            $this->visited[] = array_fill(0, $this->height, false);
        }

        return $this;
    }
}

$m = new Maze('maze.png');
$m->solve();
imagepng($m->image, 'solution.png');
