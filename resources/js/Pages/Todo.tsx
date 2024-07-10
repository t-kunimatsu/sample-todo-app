import { useGetTasks } from "@/Apis/tasks";
import TodoBoard from "@/Components/TodoBoard";
import { useTodoBoard } from "@/Components/useTodoBoard";

const Todo: React.FC = () => {
  const initializeColumns = useTodoBoard((state) => state.initializeColumns);
  const { data: tasks, error } = useGetTasks();
  if (error) return <>ERROR...</>;
  if (!tasks) return <>ROADING...</>;
  initializeColumns(tasks);

  return <TodoBoard />;
};

export default Todo;
