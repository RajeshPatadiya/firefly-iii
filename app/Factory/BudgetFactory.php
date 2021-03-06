<?php
declare(strict_types=1);
/**
 * BudgetFactory.php
 * Copyright (c) 2018 thegrumpydictator@gmail.com
 *
 * This file is part of Firefly III.
 *
 * Firefly III is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Firefly III is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Firefly III. If not, see <http://www.gnu.org/licenses/>.
 */


namespace FireflyIII\Factory;

use FireflyIII\Models\Budget;
use FireflyIII\User;
use Illuminate\Support\Collection;

/**
 * Class BudgetFactory
 */
class BudgetFactory
{
    /** @var User */
    private $user;


    /**
     * @param int|null    $budgetId
     * @param null|string $budgetName
     *
     * @return Budget|null
     */
    public function find(?int $budgetId, ?string $budgetName): ?Budget
    {
        $budgetId   = intval($budgetId);
        $budgetName = strval($budgetName);

        if (strlen($budgetName) === 0 && $budgetId === 0) {
            return null;
        }

        // first by ID:
        if ($budgetId > 0) {
            /** @var Budget $budget */
            $budget = $this->user->budgets()->find($budgetId);
            if (!is_null($budget)) {
                return $budget;
            }
        }

        if (strlen($budgetName) > 0) {
            $budget = $this->findByName($budgetName);
            if (!is_null($budget)) {
                return $budget;
            }
        }

        return null;
    }

    /**
     * @param string $name
     *
     * @return Budget|null
     */
    public function findByName(string $name): ?Budget
    {
        /** @var Collection $collection */
        $collection = $this->user->budgets()->get();
        /** @var Budget $budget */
        foreach ($collection as $budget) {
            if ($budget->name === $name) {
                return $budget;
            }
        }

        return null;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

}
