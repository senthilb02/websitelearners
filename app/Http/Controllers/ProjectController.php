<?php
    
namespace App\Http\Controllers;
    
use App\Models\Project;

use App\Models\projectUser;
use App\Models\projectuser as ModelsProjectuser;
use App\Models\User;
use Illuminate\Http\Request;
    
class ProjectController extends Controller
{ 
    
    function __construct()
    {
         $this->middleware('permission:project-list|project-create|project-edit|project-delete|project-user', ['only' => ['index','show']]);
         $this->middleware('permission:project-create', ['only' => ['create','store']]);
         $this->middleware('permission:project-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:project-delete', ['only' => ['destroy']]);
    }
    public function index()
    {
        $projects = Project::latest()->paginate(5);
        return view('project.index',compact('projects'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }
    public function create()
    {
        $users = User::where('name','!=','admin')->get(); // Retrieve all users
        return view('project.create', compact('users'));
        //return view('project.create');
    }
    public function store(Request $request)
    {
        request()->validate([
            'name' => 'required',
            'detail' => 'required',
            'user.*' =>  'required'
        ]);
        
       $requestData=$request->except(['user_id']);
       
       $projectid=Project::create($requestData);
        //dd($projectid->id);
        //$value=['project_id'=>2,'user_id'=>2];
       
        foreach($request->user as $usersv)
         {
            Projectuser::create(['project_id'=>$projectid->id,'user_id'=>(int)$usersv]);
            
         }
        
            return redirect()->route('project.index')
                        ->with('success','project created successfully.');
    }
    public function show(Project $project)
    {
        return view('project.show',compact('project'));
    }
    public function edit(Project $project)
    {
        $users = User::where('name','!=','admin')->get(); 
        return view('project.edit',compact('project','users'));
    }
    public function update(Request $request, Project $project,Projectuser $projectuser)
    {
         request()->validate([
            'name' => 'required',
            'detail' => 'required',
            'user.*' =>  'required'
        ]);
    
        $requestData=$request->except(['user_id']);
        $project->update($requestData);
        foreach($request->user as $usersv)
        {
           $projectuser->update(['project_id'=>$project->id,'user_id'=>(int)$usersv]);
           
        }
        return redirect()->route('project.index')
                        ->with('success','project updated successfully');
    }
    
    public function destroy(project $project)
    {
        $project->delete();
    
        return redirect()->route('project.index')
                        ->with('success','project deleted successfully');
    }
}