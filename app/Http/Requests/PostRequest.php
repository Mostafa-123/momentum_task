<?php

namespace App\Http\Requests;
use App\Models\Post;
use function App\apiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ];
        if (is_null($this->route('post_id'))) {
            $rule['image'] = 'required|image';
        } else {
            $id = $this->route('post_id');
            $post = Post::find($id);
            if (!$post) {
                throw new HttpResponseException(apiResponse(401, '', 'this Post_id not found'));
            }
            $rule['image'] = 'sometimes|image';
        }
        return $rules;
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(apiResponse(401, "", $validator->errors()));
    }
}
