<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * FillerGame Entity
 *
 * @property int $id
 * @property string|null $invite_code
 * @property string|null $grid
 * @property int|null $p1_id
 * @property int|null $p2_id
 * @property string|null $p1_owned
 * @property string|null $p2_owned
 * @property int|null $current_turn
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 */
class FillerGame extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'invite_code' => true,
        'grid' => true,
        'p1_id' => true,
        'p2_id' => true,
        'p1_owned' => true,
        'p2_owned' => true,
        'current_turn' => true,
        'created' => true,
        'modified' => true,
    ];
}
