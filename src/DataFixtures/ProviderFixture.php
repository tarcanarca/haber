<?php

namespace App\DataFixtures;

use App\Entity\NewsProvider;
use App\Entity\NewsProviderCategory;
use App\Types\Category;
use App\Types\ProviderType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class ProviderFixture extends Fixture
{
    public const TEST_PROVIDER_1 = "Test Provider 1";

    public function load(ObjectManager $manager)
    {
        $provider = new NewsProvider();
        $provider->setName(self::TEST_PROVIDER_1)
            ->setUrl("http://test.com")
            ->setType(ProviderType::CM_HABER());

        $category1 = new NewsProviderCategory();
        $category1->setCategory(Category::KIBRIS())
            ->setPath('kibris')
            ->setNewsProvider($provider);

        $category2 = new NewsProviderCategory();
        $category2->setCategory(Category::DUNYA())
            ->setPath('world')
            ->setNewsProvider($provider);

        $provider->setCategories([$category1, $category2]);

        $manager->persist($provider);
        $manager->flush();
    }
}
