<?php

namespace Prego\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use Prego\File;
use Prego\Task;
use Prego\Comment;
use Prego\Project;
use Prego\Collaboration;
use Prego\Http\Requests;
use Prego\Http\Controllers\Controller;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $projects = Project::personal()->get();
        return view('projects.index')->withProject($projects);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('projects.new');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name'     => 'required|min:3',
            'due-date' => 'required|date|after:today',
            'notes'    => 'required|min:10',
            'status'   => 'required'
        ]);

        $project = new Project;
        $project->project_name   = $request->input('name');
        $project->project_status = $request->input('status');
        $project->due_date       = $request->input('due-date');
        $project->project_notes  = $request->input('notes');
        $project->user_id        = Auth::user()->id;

        $project->save();

        return redirect()->route('projects.index')->with('info','Your Project has been created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $project       = Project::find($id);
        $tasks         = $this->getTasks($id);
        $files         = $this->getFiles($id);
        $comments      = $this->getComments($id);
        $collaborators = $this->getCollaborators($id);
        return view('projects.show')
                            ->withProject($project)
                            ->withTasks($tasks)
                            ->withFiles($files)
                            ->withComments($comments)
                            ->withCollaborators($collaborators);
    }

    /**
     * Get all the tasks that belongs to a project
     * @param  integer $id
     * @return collection
     */
    public function getTasks($id)
    {
        $tasks =  Task::project($id)->get();
        return $tasks;
    }

    /**
     * Get all the files that were attached to a project
     * @param  integer $id
     * @return collection
     */
    public function getFiles($id)
    {
        $files =  File::project($id)->get();
        return $files;
    }

    /**
     * Get all the comments that were made on a Project
     * @param  integer $id
     * @return collection
     */
    public function getComments($id)
    {
        $comments = Comment::project($id)->get();
        return $comments;
    }

    /**
     * Get all the collaborators on this project
     * @param  int $id
     * @return collection
     */
    public function getCollaborators($id)
    {
        $collaborators = Collaboration::project($id)->get();
        return $collaborators;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $project = Project::find($id);
        return view('projects.edit')->withProject($project);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $this->validate($request, [
            'project_name'     => 'required|min:3',
            'due-date'         => 'required|date|after:today',
            'project_notes'    => 'required|min:10',
            'project_status'   => 'required'
        ]);

        $values = $request->all();
        $project->fill($values)->save();

        return redirect()->back()->with('info','Your Project has been updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();

        return redirect()->route('projects.index')->with('info', 'Project deleted successfully');
    }
}
