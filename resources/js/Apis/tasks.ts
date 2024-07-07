import useSWR, { SWRResponse } from "swr";

export type Status = "todo" | "doing" | "done";

// TODO >> これが何か変な感じ。Taskのマップ表現でしかないので型として持つのはおかしい？APIをシンプルな形に戻すか・・・
export type Tasks = {
  todo: Task[];
  doing: Task[];
  done: Task[];
};

export type Task = {
  id: number;
  title: string;
  status: Status;
};

export type NewTask = {
  title: string;
  status: Status;
};

const fetcher = (url: string): Promise<Tasks> => fetch(url).then((res) => res.json());

export const useGetTasks = () => {
  // TODO >> Immutableにする
  return useSWR("/api/v1/tasks", fetcher);
};

// TODO >> SWRで実装する（その前にAPI修正する必要あり）
export const postTask = async (task: NewTask): Promise<Task> => {
  const { title, status } = task;
  const response = await fetch("/api/v1/tasks", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(task),
  });
  if (!response.ok) {
    // TODO >> トースト実装する
    throw new Error("Failed to add task");
  }
  return await response.json();
};

export const patchTask = async (task: Task, position?: number): Promise<Task> => {
  const { id, title, status } = task;
  const response = await fetch(`/api/v1/tasks/${id}`, {
    method: "PATCH",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ ...task, ...(position && { position }) }),
  });
  if (!response.ok) {
    // TODO >> トースト実装する
    throw new Error("Failed to update task");
  }
  return await response.json();
};
