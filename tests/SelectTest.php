<?php

trait SelectTest
{
    public function testSelects()
    {
        $db = $this->getConnection()->getConnection();
        $result = $db->select('test', '*');
        $test = [];
        foreach ($result() as $row) {
            $test[] = (int)$row['id'];
        }
        $this->assertEquals([1, 2, 3], $test);

        // Re-query should also work, yielding a new result set:
        $result = $db->select('test', '*', [], ['order' => 'id']);
        $test = [];
        foreach ($result() as $row) {
            $test[] = (int)$row['id'];
        }
        $this->assertEquals([1, 2, 3], $test);

        $db = $this->getConnection()->getConnection();
        $result = $db->fetch('test', '*', [], ['order' => 'id']);
        $this->assertEquals(1, (int)$result['id']);

        $result = $db->column('test', 'id', [], ['order' => 'id']);
        $this->assertEquals(1, (int)$result);
    }

    /**
     * @expectedException Dabble\Query\SelectException
     */
    public function testNoResults()
    {
        $db = $this->getConnection()->getConnection();
        $db->select('test', '*', ['id' => 12345]);
    }

    public function testCount()
    {
        $db = $this->getConnection()->getConnection();
        $cnt = $db->count('test');
        $this->assertEquals(3, (int)$cnt);
    }

    public function testAll()
    {
        $db = $this->getConnection()->getConnection();
        $rows = $db->fetchAll('test', '*');
        $this->assertEquals(3, count($rows));
    }

    public function testAlias()
    {
        $db = $this->getConnection()->getConnection();
        $row = $db->fetch('test', ['foo' => 'name'], ['id' => 1]);
        $this->assertEquals('foo', $row['foo']);
    }

    public function testSubquery()
    {
        $db = $this->getConnection()->getConnection();
        $row = $db->fetch(
            'test',
            '*',
            ['id' => new Dabble\Query\Select(
                $db,
                'test2',
                ['test'],
                new Dabble\Query\Where(['data' => 'lorem ipsum']),
                new Dabble\Query\Options
            )]
        );
        $this->assertEquals('foo', $row['name']);
    }
}

