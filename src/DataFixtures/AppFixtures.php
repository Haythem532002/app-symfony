<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Comment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 1; $i <= 3; $i++) {
            // Créer 3 fausses catégories
            $category = new Category();
            $category->setTitle(implode(' ', $faker->words(3)))
                     ->setDescription(implode("\n", $faker->paragraphs(1)));
            $manager->persist($category);

            for ($j = 1; $j <= mt_rand(4, 6); $j++) {
                // Créer entre 4 et 6 articles pour chaque catégorie
                $article = new Article();
                $article->setTitle(implode(' ', $faker->words(5)))
                        ->setContent(implode("\n", $faker->paragraphs(5)))
                        ->setImage($faker->imageUrl())
                        ->setCreatedat(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-2 months')))
                        ->setCategory($category);
                $manager->persist($article);

                for ($k = 1; $k <= mt_rand(4, 6); $k++) {
                    // Créer entre 4 et 6 commentaires pour chaque article
                    $comment = new Comment();
                    $days = (new \DateTimeImmutable())->diff($article->getCreatedat())->days;

                    $comment->setAuthor($faker->name())
                            ->setContent(implode(' ', $faker->words(10)))
                            ->setCreatedat(\DateTimeImmutable::createFromMutable(
                                $faker->dateTimeBetween('-' . $days . ' days')
                            ))
                            ->setArticle($article);
                    $manager->persist($comment);
                }
            }
        }

        $manager->flush();
    }
}
