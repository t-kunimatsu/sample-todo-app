import { HttpResponse, http } from "msw";
import { setupServer } from "msw/node";
import { act, renderHook, waitFor } from "@testing-library/react";
import { deleteTask, NewTask, patchTask, postTask, Task, useGetTasks } from "./tasks";

const server = setupServer();
beforeAll(() => server.listen({ onUnhandledRequest: "error" }));
afterAll(() => server.close());
afterEach(() => server.resetHandlers());

describe("useGetTasks", () => {
  it("should fetch tasks successfully", async () => {
    const tasks = [
      { id: 1, title: "Task 1", status: "todo" },
      { id: 2, title: "Task 2", status: "doing" },
    ];
    server.use(
      http.get("/api/v1/tasks", () => {
        return HttpResponse.json(tasks);
      })
    );
    const { result } = renderHook(() => useGetTasks());
    await act(async () => {
      await waitFor(() => result.current.isValidating === false);
    });
    expect(result.current.data).toEqual(tasks);
    expect(result.current.error).toBeNull;
  });

  it("should handle error", async () => {
    server.use(
      http.get("/api/v1/tasks", () => {
        return new HttpResponse(null, {
          status: 404,
        });
      })
    );
    const { result } = renderHook(() => useGetTasks());
    await act(async () => {
      await waitFor(() => result.current.isValidating === false);
    });
    expect(result.current.data).toBeNull;
    expect(result.current.error).toBeDefined;
  });
});

describe("postTask", () => {
  it("should post task successfully", async () => {
    const task: NewTask = { title: "Task 1", status: "todo" };
    const responseTask = [{ id: 1, title: "Task 1", status: "todo" }];
    server.use(
      http.post("/api/v1/tasks", () => {
        return HttpResponse.json(responseTask);
      })
    );
    const result = await postTask(task);
    expect(result.task).toEqual(responseTask);
    expect(result.status).toBe(200);
  });

  it("should handle axios error", async () => {
    const task: NewTask = { title: "Task 1", status: "todo" };
    server.use(
      http.post("/api/v1/tasks", () => {
        return new HttpResponse(JSON.stringify({ errors: ["bad request"] }), {
          headers: { "Content-Type": "application/json" },
          status: 400,
        });
      })
    );
    const result = await postTask(task);
    expect(result.task).toBeNull;
    expect(result.status).toBe(400);
    expect(result.errors).toEqual(["bad request"]);
  });

  it("should handle network error", async () => {
    const task: NewTask = { title: "Task 1", status: "todo" };
    const result = await postTask(task);
    expect(result.task).toBeNull;
    expect(result.status).toBe(500);
    expect(result.errors).toEqual(["予期せぬエラーが発生しました。"]);
  });
});

describe("patchTask", () => {
  it("should patch task successfully", async () => {
    const task: Task = { id: 1, title: "Task 1", status: "todo" };
    server.use(
      http.patch("/api/v1/tasks/1", () => {
        return HttpResponse.json("", {
          status: 200,
        });
      })
    );
    const result = await patchTask(task);
    expect(result.status).toBe(200);
  });

  it("should patch task with position successfully", async () => {
    const task: Task = { id: 2, title: "Task 2", status: "todo" };
    server.use(
      http.patch("/api/v1/tasks/2", () => {
        return HttpResponse.json("", {
          status: 200,
        });
      })
    );
    const result = await patchTask(task, 123);
    expect(result.status).toBe(200);
  });

  it("should handle axios error", async () => {
    const task: Task = { id: 2, title: "Task 2", status: "todo" };
    server.use(
      http.patch("/api/v1/tasks/2", () => {
        return new HttpResponse(JSON.stringify({ errors: ["bad request"] }), {
          headers: { "Content-Type": "application/json" },
          status: 400,
        });
      })
    );
    const result = await patchTask(task);
    expect(result.task).toBeNull;
    expect(result.status).toBe(400);
    expect(result.errors).toEqual(["bad request"]);
  });

  it("should handle network error", async () => {
    const task: Task = { id: 3, title: "Task 3", status: "todo" };
    const result = await patchTask(task);
    expect(result.task).toBeNull;
    expect(result.status).toBe(500);
    expect(result.errors).toEqual(["予期せぬエラーが発生しました。"]);
  });
});

describe("deleteTask", () => {
  it("should delete task successfully", async () => {
    server.use(
      http.delete("/api/v1/tasks/1", () => {
        return HttpResponse.json("", {
          status: 200,
        });
      })
    );
    const result = await deleteTask(1);
    expect(result.status).toBe(200);
  });

  it("should handle axios error", async () => {
    server.use(
      http.delete("/api/v1/tasks/2", () => {
        return new HttpResponse(JSON.stringify({ errors: ["not found"] }), {
          headers: { "Content-Type": "application/json" },
          status: 404,
        });
      })
    );
    const result = await deleteTask(2);
    expect(result.task).toBeNull;
    expect(result.status).toBe(404);
    expect(result.errors).toEqual(["not found"]);
  });

  it("should handle network error", async () => {
    const result = await deleteTask(1);
    expect(result.task).toBeNull;
    expect(result.status).toBe(500);
    expect(result.errors).toEqual(["予期せぬエラーが発生しました。"]);
  });
});
