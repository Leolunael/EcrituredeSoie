<?php

namespace App\Tests\Document;

use App\Document\Avis;
use PHPUnit\Framework\TestCase;

class AvisTest extends TestCase
{
    public function testAvisNonApprouveParDefaut(): void
    {
        $avis = new Avis();

        $this->assertFalse($avis->isApprouve());
    }

    public function testAvisSansReponse(): void
    {
        $avis = new Avis();

        $this->assertFalse($avis->hasReponse());
    }

    public function testSetNote(): void
    {
        $avis = new Avis();
        $avis->setNote(5);

        $this->assertEquals(5, $avis->getNote());
    }
}

