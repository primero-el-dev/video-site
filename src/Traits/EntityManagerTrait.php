<?php

namespace App\Traits;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Service\Attribute\Required;

trait EntityManagerTrait
{
	protected EntityManagerInterface $em;

	#[Required]
	public function setDocumentManager(EntityManagerInterface $em): void
	{
		$this->em = $em;
	}
}