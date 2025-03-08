<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MemberSelectorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_checks_members_when_group_is_selected()
    {
        // Arrange: Set up the initial state
        $branchGroups = [
            [
                'id' => 5,
                'name' => '歴史探索グループ',
                'members' => [
                    ['id' => 11, 'name' => 'さだ']
                ]
            ],
            [
                'id' => 6,
                'name' => '飲み歩き',
                'members' => [
                    ['id' => 12, 'name' => 'test']
                ]
            ]
        ];

        $members = [
            ['id' => 9, 'name' => 'test'],
            ['id' => 10, 'name' => 'さだ']
        ];

        $initialSelectedMembers = [];

        // Act: Simulate selecting a group
        $selectedMembers = [];
        foreach ($branchGroups as $group) {
            foreach ($group['members'] as $member) {
                $selectedMembers[$member['id']] = true;
            }
        }

        // Assert: Check if the members are selected
        $this->assertTrue($selectedMembers[11]);
        $this->assertFalse($selectedMembers[9]);
    }
} 