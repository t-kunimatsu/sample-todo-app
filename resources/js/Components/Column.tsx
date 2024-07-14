import { useDroppable } from "@dnd-kit/core";
import { SortableContext, verticalListSortingStrategy } from "@dnd-kit/sortable";
import { AddCircleOutline } from "@mui/icons-material";
import { Box, Button, Typography } from "@mui/material";
import Card from "./Card";
import { useTodoBoard } from "./useTodoBoard";
import { Task, Status } from "@/Apis/tasks";
import { useShallow } from "zustand/react/shallow";

export type ColumnProps = {
  id: Status;
  title: string;
  tasks: Task[];
  showAddTask: boolean;
};

const Column: React.FC<ColumnProps> = (props) => {
  const { id, title, tasks, showAddTask } = props;
  const { setNodeRef } = useDroppable({ id: id });
  const { setCurrentColumnId, setCurrentCard, setDialogOpen, setDialogMode, resetCardDialogForm } =
    useTodoBoard(
      useShallow((state) => ({
        setCurrentColumnId: state.setCurrentColumnId,
        setCurrentCard: state.setCurrentCard,
        setDialogOpen: state.setDialogOpen,
        setDialogMode: state.setDialogMode,
        resetCardDialogForm: state.resetCardDialogForm,
      }))
    );

  const handleDialogOpen = (columnId: Status) => {
    setDialogMode("add");
    setCurrentColumnId(columnId);
    setCurrentCard({ title: "", status: id });
    resetCardDialogForm && resetCardDialogForm({ title: "" });
    setDialogOpen(true);
  };

  return (
    <SortableContext id={id} items={tasks} strategy={verticalListSortingStrategy}>
      <Box
        ref={setNodeRef}
        sx={{
          flex: "1",
          background: "rgba(245,247,249,1.00)",
          marginRight: "10px",
          padding: "8px",
          minHeight: "90dvh",
        }}
      >
        <Box
          sx={{
            display: "flex",
            justifyContent: "space-between",
            alignItems: "center",
            height: "24px",
            padding: "2px",
            marginBottom: "16px",
          }}
        >
          <Typography
            variant="h6"
            sx={{
              fontWeight: "800",
              color: "#575757",
            }}
          >
            {title}
          </Typography>
          {showAddTask && (
            <Button
              variant="contained"
              onClick={() => handleDialogOpen(id)}
              sx={{ padding: "4px", margin: "4px", minWidth: "32px" }}
              data-testid={`add-button-${id}`}
            >
              <AddCircleOutline />
            </Button>
          )}
        </Box>
        {tasks.map((task) => (
          <Box key={task.id} sx={{ display: "flex", alignItems: "center", padding: "4px" }}>
            <Card {...task} />
          </Box>
        ))}
      </Box>
    </SortableContext>
  );
};

export default Column;
