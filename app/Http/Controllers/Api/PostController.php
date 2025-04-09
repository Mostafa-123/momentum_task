<?php

namespace App\Http\Controllers\Api;

use Exception;
use Carbon\Carbon;
use App\Models\Post;
use App\Models\User;
use function App\postData;

use function App\apiResponse;
use App\Traits\ManageFileTrait;
use App\Http\Requests\PostRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    use ManageFileTrait;

    public function createPost(PostRequest $request){
        $user=User::find(Auth::user()->id);
        if($user){
            try{
                DB::beginTransaction();
                $post=Post::create([
                    'title' =>$request->title,
                    'content' =>$request->content,
                    'image' =>$this->uploadFile($request,'image','PostPhoto'),
                    'user_id' =>Auth::user()->id,
                ]);
                DB::commit();
                return apiResponse(201,postData($post),"post created successfully");
            }catch(Exception $e){
                DB::rollBack();
                return apiResponse(401,'',$e);
            }
        }return apiResponse(401,'',"This User id not found");
    }

    public function updatePost(PostRequest $request , $post_id ){
        $post=Post::find($post_id);
        if($post){
            if ($post->user_id !== Auth::user()->id) {
                return apiResponse(401,'',"Unauthorized action");
            }    
            $image=$request->image;
                if($image && $post->image){
                    $this->deleteFile($post->image);
                    $image=$this->uploadFile($request,'image','PostPhoto');
                }elseif($image != null && $post->image == null){
                    $image=$this->uploadFile($request,'image','PostPhoto');
                }else{
                    $image=$post->image;
                }
            $post->update([
                'title' =>$request->title,
                'content' =>$request->content,
                'image' =>$image,
                'user_id' =>Auth::user()->id,
            ]);
            $post->save();
            return apiResponse(201,postData($post),"post updated successfully");
        }return apiResponse(401,'',"This Post id not found");
    }
    
    public function deletePostSoftly($post_id){
        $post=Post::find($post_id);
        if($post){
            if ($post->user_id !== Auth::user()->id) {
                return apiResponse(401,'',"Unauthorized action");
            }   
            $post->delete();
            return apiResponse(201,'',"post deleted successfully");
        }return apiResponse(401,'',"This Post id not found");
    }

    public function viewUserPosts($user_id){
        $user=User::find($user_id);
        if($user){
            if ($user->id !== Auth::user()->id) {
                return apiResponse(401,'',"Unauthorized action");
            }
            $posts = $user->posts;
                if(!$posts->isEmpty()) {
                    foreach($posts as $post){
                        $data[]=postData($post);
                    }
                    return apiResponse(201,$data,"User posts");
                }return apiResponse(401,'',"this user doesn't have posts yet ");
        }return apiResponse(401,'',"This User id not found");
    }

    public function viewUserPost($post_id){
        $post=Post::find($post_id);
        if($post){
            if ($post->user_id !== Auth::user()->id) {
                return apiResponse(401,'',"Unauthorized action");
            }
            return apiResponse(201,postData($post),"User post");    
        }return apiResponse(401,'',"This Post id not found");
    }

    public function viewDeletedPosts($user_id){
        $user=User::find($user_id);
        if($user){
            if ($user->id !== Auth::user()->id) {
                return apiResponse(401,'',"Unauthorized action");
            }
            $deletedPosts=Post::onlyTrashed()->where('user_id', $user->id)->get();
            if($deletedPosts){
                foreach($deletedPosts as $deletedpost){
                    $data[]=postData($deletedpost);
                }
            return apiResponse(201,$data,"This is deleted posts");
        }return apiResponse(401,'',"This User doesn't has deleted posts");
    }return apiResponse(401,'',"This User id not found");
}

    public function restoreDeletedPost($post_id){
        $post=Post::onlyTrashed()->where('id' , $post_id)->get();
        $user_id=Auth::user()->id;
        if(!$post->isEmpty()){
            if ($post[0]['user_id'] !== $user_id) {
                return apiResponse(401,'',"Unauthorized action");
            }   
            $post = Post::onlyTrashed()->where('id', $post_id)->where('user_id', $user_id)->first();
            $post->restore();
            $post=Post::find($post_id);
            return apiResponse(201,postData($post),"post restored successfully");
        }return apiResponse(401,'',"This Post id not found");
    }

    public function getPostImage($post_id){
        $post=Post::find($post_id);
        if($post){
            if($post->image){
                return $this->getFile($post->image); 
            }
            return apiResponse(401, '', "This post doesn't has image");
        }
        return apiResponse(401, '', 'this post id not found');
    }

    public function stats(){
        $userCount=User::count();
        $postCount=Post::count();
        $usersWithNoPosts=User::whereDoesntHave('posts')->count();
        $data=[
            'user_count' => $userCount,
            'post_count' => $postCount,
            'users_with_no_posts_count' => $usersWithNoPosts,
        ];
        return apiResponse(201,$data,"Stats of system");
    }
}
