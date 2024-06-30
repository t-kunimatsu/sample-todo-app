<?php

declare(strict_types=1);

namespace App\Http\Requests\Task;

use App\Contexts\Task\Domain\Value\Status;
use App\Contexts\Task\Domain\Value\Title;
use App\Http\Requests\ApiFormRequest;

class StoreRequest extends ApiFormRequest
{
    /**
     * @return array<array>
     */
    public function rules(): array
    {
        return [
            'title' => Title::RULE,
            'status' => Status::RULE,
        ];
    }

    /**
     * @return array
     */
    public function messages(): array
    {
        return array_merge(
            Title::MESSAGES,
            Status::MESSAGES,
        );
    }
}
