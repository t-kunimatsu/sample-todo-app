import axios, { AxiosError, isAxiosError } from "axios";
import useSWRImmutable from "swr/immutable";

export type Status = "todo" | "doing" | "done";

export type Task = {
  id: number;
  title: string;
  status: Status;
};

export type NewTask = {
  title: string;
  status: Status;
};

export type ApiResult = {
  status: number;
  task?: Task;
  errors?: string[];
};

const fetcher = (url: string): Promise<Task[]> => axios(url).then((res) => res.data);

export const useGetTasks = () => {
  return useSWRImmutable("/api/v1/tasks", fetcher);
};

export const postTask = async (task: NewTask): Promise<ApiResult> => {
  try {
    const response = await axios.post("/api/v1/tasks", task);
    return {
      status: 200,
      task: response.data,
    };
  } catch (e) {
    if (isAxiosError(e)) {
      return {
        status: e.response?.status ?? 500,
        errors: e.response?.data.errors,
      };
    }
  }
  return {
    status: 500,
    errors: ["予期せぬエラーが発生しました。"],
  };
};

export const patchTask = async (task: Task, position?: number): Promise<ApiResult> => {
  const { id } = task;
  try {
    const response = await axios.patch(`/api/v1/tasks/${id}`, {
      ...task,
      ...(position !== undefined && { position }),
    });
    return {
      status: 200,
      task: response.data,
    };
  } catch (e) {
    if (isAxiosError(e)) {
      return {
        status: e.response?.status ?? 500,
        errors: e.response?.data.errors,
      };
    }
  }
  return {
    status: 500,
    errors: ["予期せぬエラーが発生しました。"],
  };
};
